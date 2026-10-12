<?php

namespace App\Services\Eggs\Catalog;

use App\Models\Egg;
use App\Models\EggVariable;
use App\Rules\EggVariableRules;
use App\Services\Eggs\EggParserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CatalogDefinition
{
    public function __construct(private EggParserService $parser) {}

    public function withFile(string $bytes, callable $callback): mixed
    {
        $path = tempnam(sys_get_temp_dir(), 'reviactyl-egg-');
        if ($path === false) {
            throw new CatalogException('import');
        }
        try {
            chmod($path, 0600);
            if (file_put_contents($path, $bytes) !== strlen($bytes)) {
                throw new CatalogException('import');
            }

            return $callback(new UploadedFile($path, 'egg.json', 'application/json', null, true));
        } finally {
            unlink($path);
        }
    }

    public function parse(string $bytes): array
    {
        try {
            $data = $this->withFile($bytes, fn (UploadedFile $file) => $this->parser->handle($file));
            $egg = $this->parser->fillFromParsed(new Egg(), $data);
            $attributes = $egg->attributesToArray() + ['author' => $data['author'] ?? null];
            $rules = Arr::except(Egg::getRules(), ['nest_id', 'uuid']);
            Validator::make($attributes, $rules)->validate();
            Validator::make($data, [
                'scripts.installation' => 'required|array',
                'scripts.installation.script' => 'present|nullable|string',
                'scripts.installation.entrypoint' => 'required|string|max:191',
                'scripts.installation.container' => 'required|string|max:191',
            ])->validate();
            foreach ($data['variables'] as $variable) {
                Validator::make($variable, Arr::except(EggVariable::getRules(), ['egg_id']))->validate();
                Validator::make(['rules' => explode('|', $variable['rules'])], ['rules' => [new EggVariableRules()]])->validate();
            }

            return $data;
        } catch (\Throwable $exception) {
            $decoded = json_decode($bytes, true);
            $variables = is_array($decoded) && is_array($decoded['variables'] ?? null) ? $decoded['variables'] : [];
            $names = [];
            foreach ($variables as $variable) {
                if (! is_array($variable) || ! is_string($variable['env_variable'] ?? null)) {
                    continue;
                }
                if (in_array($variable['env_variable'], $names, true)) {
                    throw new CatalogException('duplicate_variables');
                }
                $names[] = $variable['env_variable'];
            }
            throw new CatalogException('definition');
        }
    }
}
