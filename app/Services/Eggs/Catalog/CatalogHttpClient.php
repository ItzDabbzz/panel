<?php

namespace App\Services\Eggs\Catalog;

use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

class CatalogHttpClient
{
    public const EGG_LIMIT = 1048576;

    public function request(PendingRequest $request, int $limit): PendingRequest
    {
        return $request->acceptJson()->withUserAgent('Reviactyl-Egg-Catalog')->connectTimeout(3)->timeout(10)->withoutRedirecting()
            ->withOptions([
                'verify' => true, 'stream' => true, 'read_timeout' => 10, 'decode_content' => false,
                'progress' => function (int $downloadTotal, int $downloaded) use ($limit): void {
                    if ($downloaded > $limit || $downloadTotal > $limit) {
                        throw new CatalogException('oversized');
                    }
                },
                'on_headers' => function (ResponseInterface $response) use ($limit): void {
                    if ((int) $response->getHeaderLine('Content-Length') > $limit) {
                        throw new CatalogException('oversized');
                    }
                },
            ]);
    }

    public function get(string $url, int $limit, bool $definition = false): string
    {
        try {
            return $this->body($this->request(Http::withOptions([]), $limit)->get($url), $limit, $definition);
        } catch (CatalogException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw $this->failure($exception);
        }
    }

    public function body(Response|\Throwable $response, int $limit, bool $definition = false): string
    {
        if (! $response instanceof Response) {
            throw $this->failure($response);
        }
        if ($response->status() === 429 || $response->status() === 403 && $response->header('X-RateLimit-Remaining') === '0') {
            $retryAfter = $response->header('Retry-After');
            $delay = (int) $retryAfter;
            if ($retryAfter !== '' && ! is_numeric($retryAfter)) {
                try {
                    $delay = Carbon::parse($retryAfter)->timestamp - now()->timestamp;
                } catch (\Throwable $exception) {
                    $delay = 60;
                }
            }
            $retry = max(60, $delay, (int) $response->header('X-RateLimit-Reset') - now()->timestamp);
            throw new CatalogException('rate_limited', min(86400, $retry));
        }
        if ($definition && $response->status() === 404) {
            throw new CatalogException('unknown');
        }
        if (! $response->successful()) {
            throw new CatalogException();
        }
        $stream = $response->toPsrResponse()->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        $bytes = '';
        $deadline = microtime(true) + 10;
        while (true) {
            if (microtime(true) > $deadline) {
                throw new CatalogException();
            }
            $chunk = $stream->read(min(8192, $limit + 1 - strlen($bytes)));
            if ($chunk === '') {
                if (! $stream->eof()) {
                    throw new CatalogException();
                }
                break;
            }
            $bytes .= $chunk;
            if (strlen($bytes) > $limit) {
                throw new CatalogException('oversized');
            }
        }

        return $bytes;
    }

    private function failure(\Throwable $exception): CatalogException
    {
        do {
            if ($exception instanceof CatalogException) {
                return $exception;
            }
        } while ($exception = $exception->getPrevious());

        return new CatalogException();
    }

    public function json(string $bytes): array
    {
        try {
            $data = json_decode($bytes, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new CatalogException('invalid_catalog');
        }
        if (! is_array($data)) {
            throw new CatalogException('invalid_catalog');
        }

        return $data;
    }
}
