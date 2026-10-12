<?php

namespace Tests\Unit\Services\Eggs\Catalog;

use App\Services\Eggs\Catalog\CatalogException;
use App\Services\Eggs\Catalog\CatalogHttpClient;
use App\Services\Eggs\Catalog\CatalogUrl;
use App\Services\Eggs\Catalog\EggCatalogService;
use App\Services\Eggs\Catalog\PterodactylProvider;
use App\Services\Eggs\Catalog\RextstoreProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;

class EggCatalogServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    public static function record(string $repo = 'game-eggs', string $id = 'paper'): array
    {
        return ['id' => $id, 'name' => 'Paper', 'description' => 'Template', 'category' => 'games',
            'repo' => 'https://github.com/pterodactyl/'.$repo, 'filePath' => 'test/egg-paper.json',
            'downloadUrl' => 'https://raw.githubusercontent.com/pterodactyl/'.$repo.'/main/test/egg-paper.json'];
    }

    public function test_combined_index_normalizes_all_repositories_and_skips_invalid_records(): void
    {
        Http::fake([PterodactylProvider::INDEX_URL => Http::response([
            self::record(), self::record('application-eggs', 'app'), self::record('generic-eggs', 'language'), ['invalid' => true],
        ])]);
        $result = app(PterodactylProvider::class)->fetch();
        $this->assertSame(['Games', 'Applications', 'Languages'], array_column($result['entries'], 'category'));
        $this->assertSame(1, $result['invalid']);
        $this->assertSame('paper', $result['entries'][0]->id);
        Http::assertSentCount(1);
    }

    public function test_rextstore_discovers_only_egg_blobs_and_reuses_immutable_definitions(): void
    {
        $sha = str_repeat('a', 40);
        Http::fake([
            RextstoreProvider::TREE_URL => Http::response(['sha' => $sha, 'truncated' => false, 'tree' => [
                ['type' => 'blob', 'path' => 'Minecraft/egg-paper.json'], ['type' => 'blob', 'path' => 'metadata.json'],
                ['type' => 'tree', 'path' => 'egg-folder.json'],
            ]]),
            'https://raw.githubusercontent.com/reviactyl/eggs/*' => Http::response(file_get_contents(base_path('tests/Fixtures/egg-catalog/egg.json'))),
        ]);
        $provider = app(RextstoreProvider::class);
        $entry = $provider->fetch()['entries'][0];
        $this->assertSame('Minecraft/egg-paper.json', $entry->id);
        $this->assertSame('Minecraft', $entry->category);
        $this->assertSame('fixtures@example.com', $entry->author);
        $this->assertSame('https://rextstore.app/eggs/?nest=Minecraft&egg=paper', $entry->sourceUrl);
        $this->assertStringContainsString($sha, $entry->downloadUrl());
        $provider->definition($entry);
        $provider->fetch();
        Http::assertSentCount(3);
    }

    public function test_truncated_tree_is_an_error(): void
    {
        Http::fake([RextstoreProvider::TREE_URL => Http::response(['sha' => str_repeat('a', 40), 'truncated' => true, 'tree' => []])]);
        $this->expectException(CatalogException::class);
        app(RextstoreProvider::class)->fetch();
    }

    public function test_rextstore_definition_failure_preserves_the_complete_saved_catalog(): void
    {
        $firstSha = str_repeat('a', 40);
        $nextSha = str_repeat('b', 40);
        $tree = ['truncated' => false, 'tree' => [
            ['type' => 'blob', 'path' => 'Minecraft/egg-paper.json'],
            ['type' => 'blob', 'path' => 'Minecraft/egg-other.json'],
        ]];
        $bytes = file_get_contents(base_path('tests/Fixtures/egg-catalog/egg.json'));
        Http::fake([
            RextstoreProvider::TREE_URL => Http::sequence()
                ->push(['sha' => $firstSha] + $tree)->push(['sha' => $nextSha] + $tree),
            'https://raw.githubusercontent.com/reviactyl/eggs/'.$firstSha.'/*' => Http::response($bytes),
            'https://raw.githubusercontent.com/reviactyl/eggs/'.$nextSha.'/Minecraft/egg-paper.json' => Http::response($bytes),
            'https://raw.githubusercontent.com/reviactyl/eggs/'.$nextSha.'/Minecraft/egg-other.json' => Http::response('', 503),
        ]);
        $catalog = app(EggCatalogService::class);
        $first = $catalog->provider('Rextstore');
        $this->assertCount(2, $first['entries']);
        $this->assertNull($first['error']);
        $this->travel(61)->seconds();

        $failed = $catalog->provider('Rextstore', true);

        $this->assertSame('unavailable', $failed['error']);
        $this->assertTrue($failed['stale']);
        $this->assertSame($first['refreshed_at'], $failed['refreshed_at']);
        $this->assertSame($first['entries'], $failed['entries']);
        $this->assertSame(0, $failed['invalid']);
        $this->assertSame($firstSha, $catalog->resolve('Rextstore', 'Minecraft/egg-other.json')->revision);
    }

    public static function rejectedUrls(): array
    {
        $base = 'https://raw.githubusercontent.com/pterodactyl/game-eggs/main/test/egg-paper.json';

        return array_map(fn ($url) => [$url], [
            str_replace('https:', 'http:', $base), str_replace('raw.githubusercontent.com', 'raw.githubusercontent.com.evil.test', $base),
            str_replace('https://', 'https://user:pass@', $base), str_replace('.com/', '.com:443/', $base),
            $base.'?', $base.'#fragment', str_replace('game-eggs/', 'game-eggs-evil/', $base),
            str_replace('/test/', '/test/../', $base), str_replace('/test/', '/test%2f', $base),
            str_replace('/test/', '/%74est/', $base), str_replace('/test/', '/test\\', $base),
            str_replace('/main/', '/../../', $base),
        ]);
    }

    #[DataProvider('rejectedUrls')]
    public function test_download_urls_reject_noncanonical_or_untrusted_components(string $url): void
    {
        $this->expectException(CatalogException::class);
        CatalogUrl::validate($url, 'pterodactyl/game-eggs', 'test/egg-paper.json');
    }

    public function test_spaces_are_encoded_without_loosening_repository_boundaries(): void
    {
        $url = 'https://raw.githubusercontent.com/pterodactyl/generic-eggs/main/My Language/egg-test.json';
        $this->assertSame(str_replace(' ', '%20', $url), CatalogUrl::validate($url, 'pterodactyl/generic-eggs', 'My Language/egg-test.json'));
    }

    public function test_literal_hash_in_repository_metadata_is_encoded_as_a_filename_not_a_fragment(): void
    {
        $url = 'https://raw.githubusercontent.com/pterodactyl/generic-eggs/main/c%23/egg-generic-c.json';
        $this->assertSame($url, CatalogUrl::validate($url, 'pterodactyl/generic-eggs', 'c#/egg-generic-c.json'));
    }

    public function test_catalog_is_fresh_for_thirty_minutes_then_refreshes(): void
    {
        Http::fake([PterodactylProvider::INDEX_URL => Http::response([self::record()])]);
        $catalog = app(EggCatalogService::class);
        $catalog->provider('Pterodactyl');
        $this->travel(29)->minutes();
        $catalog->provider('Pterodactyl');
        Http::assertSentCount(1);
        $this->travel(1)->minutes();
        $this->assertTrue($catalog->cached('Pterodactyl')['stale']);
        $catalog->provider('Pterodactyl');
        Http::assertSentCount(2);
        $this->assertFalse($catalog->cached('Pterodactyl')['stale']);
    }

    public function test_fresh_cache_and_failed_refresh_preserve_last_good_catalog_independently(): void
    {
        Http::fake([
            PterodactylProvider::INDEX_URL => Http::sequence()->push([self::record()])->push('', 429, ['Retry-After' => '120']),
            RextstoreProvider::TREE_URL => Http::response('', 503),
        ]);
        $catalog = app(EggCatalogService::class);
        $first = $catalog->provider('Pterodactyl');
        $this->assertNull($first['error']);
        $catalog->provider('Pterodactyl', true);
        Http::assertSentCount(1);
        $this->travel(61)->seconds();
        $failed = $catalog->provider('Pterodactyl', true);
        $this->assertTrue($failed['stale']);
        $this->assertSame($first['refreshed_at'], $failed['refreshed_at']);
        $this->assertCount(1, $failed['entries']);
        $this->assertSame('rate_limited', $failed['error']);
        $this->assertSame('unavailable', $catalog->provider('Rextstore')['error']);
        $catalog->provider('Pterodactyl', true);
        Http::assertSentCount(3);
        $this->travel(25)->hours();
        $this->assertSame([], $catalog->cached('Pterodactyl')['entries']);
    }

    public static function invalidIndexes(): array
    {
        return [['false'], ['{"error":true}'], ['not json'], ['[{"invalid":true}]']];
    }

    #[DataProvider('invalidIndexes')]
    public function test_invalid_indexes_are_never_cached_as_empty_success(string $bytes): void
    {
        Http::fake([PterodactylProvider::INDEX_URL => Http::response($bytes)]);
        $status = app(EggCatalogService::class)->provider('Pterodactyl');
        $this->assertSame('invalid_catalog', $status['error']);
        $this->assertNull($status['refreshed_at']);
    }

    public function test_provider_refresh_lock_prevents_duplicate_network_work(): void
    {
        Http::fake();
        $lock = Cache::lock('egg-catalog:provider:Pterodactyl:lock', 300);
        $this->assertTrue($lock->get());
        $status = app(EggCatalogService::class)->provider('Pterodactyl', true);
        $this->assertSame('refreshing', $status['error']);
        Http::assertNothingSent();
        $lock->release();
    }

    public function test_unknown_id_does_not_fetch_an_arbitrary_download(): void
    {
        Http::fake([PterodactylProvider::INDEX_URL => Http::response([self::record()])]);
        try {
            app(EggCatalogService::class)->provider('Pterodactyl');
            app(EggCatalogService::class)->resolve('Pterodactyl', 'https://evil.example/egg.json');
            $this->fail('Unknown ID resolved');
        } catch (CatalogException $exception) {
            $this->assertSame('unknown', $exception->reason);
            Http::assertSentCount(1);
        }
    }

    public function test_valid_empty_index_is_a_success(): void
    {
        Http::fake([PterodactylProvider::INDEX_URL => Http::response([])]);
        $status = app(EggCatalogService::class)->provider('Pterodactyl');
        $this->assertNull($status['error']);
        $this->assertNotNull($status['refreshed_at']);
    }

    public function test_redirects_are_rejected_and_requests_have_bounded_verified_options(): void
    {
        $optionsSeen = [];
        Http::fake(function ($request, $options) use (&$optionsSeen) {
            $optionsSeen = $options;

            return Http::response('', 302, ['Location' => 'https://evil.test']);
        });
        try {
            app(CatalogHttpClient::class)->get(PterodactylProvider::INDEX_URL, 100);
            $this->fail('Redirect accepted');
        } catch (CatalogException) {
            $this->assertFalse($optionsSeen['allow_redirects']);
            $this->assertTrue($optionsSeen['verify']);
            $this->assertSame(3, $optionsSeen['connect_timeout']);
            $this->assertSame(10, $optionsSeen['timeout']);
            Http::assertSentCount(1);
        }
    }

    public function test_http_date_rate_limit_blocks_refresh_until_the_requested_time(): void
    {
        Http::fake([PterodactylProvider::INDEX_URL => Http::response('', 429, ['Retry-After' => now()->addMinutes(5)->toRfc7231String()])]);
        $catalog = app(EggCatalogService::class);
        $this->assertSame('rate_limited', $catalog->provider('Pterodactyl')['error']);
        $this->travel(120)->seconds();
        $catalog->provider('Pterodactyl', true);
        Http::assertSentCount(1);
    }

    public function test_removed_definition_reports_refresh_instead_of_a_generic_provider_error(): void
    {
        Http::fake(['*' => Http::response('', 404)]);
        try {
            app(CatalogHttpClient::class)->get(self::record()['downloadUrl'], CatalogHttpClient::EGG_LIMIT, definition: true);
            $this->fail('Removed definition accepted');
        } catch (CatalogException $exception) {
            $this->assertSame('unknown', $exception->reason);
        }
    }

    public function test_oversized_header_rejection_survives_transport_wrapping(): void
    {
        $client = app(CatalogHttpClient::class);
        $cause = new CatalogException('oversized');
        try {
            $client->body(new \RuntimeException('Transport wrapper', 0, $cause), CatalogHttpClient::EGG_LIMIT);
            $this->fail('Oversize failure ignored');
        } catch (CatalogException $exception) {
            $this->assertSame('oversized', $exception->reason);
        }
        $options = [];
        Http::fake(function ($request, $requestOptions) use (&$options) {
            $options = $requestOptions;

            return Http::response('ok');
        });
        $client->get(PterodactylProvider::INDEX_URL, 100);
        $this->expectException(CatalogException::class);
        $options['on_headers'](new Response(200, ['Content-Length' => '101']));
    }

    public function test_a_stalled_nonseekable_stream_fails_without_spinning_or_accepting_partial_bytes(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isSeekable')->willReturn(false);
        $stream->method('eof')->willReturn(false);
        $stream->expects($this->once())->method('read')->willReturn('');
        $this->expectException(CatalogException::class);
        app(CatalogHttpClient::class)->body(new \Illuminate\Http\Client\Response(new Response(200, [], $stream)), 100);
    }

    public function test_body_without_content_length_and_chunked_sink_are_bounded(): void
    {
        $client = app(CatalogHttpClient::class);
        try {
            $client->body(new \Illuminate\Http\Client\Response(new Response(200, [], str_repeat('a', 101))), 100);
            $this->fail('Oversized body accepted');
        } catch (CatalogException $exception) {
            $this->assertSame('oversized', $exception->reason);
        }
        $options = [];
        Http::fake(function ($request, $requestOptions) use (&$options) {
            $options = $requestOptions;

            return Http::response('ok');
        });
        $client->get(PterodactylProvider::INDEX_URL, 100);
        $this->expectException(CatalogException::class);
        $options['progress'](0, 101);
    }
}
