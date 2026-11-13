<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'label'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        $value = $setting->value;

        return is_array($value) && array_key_exists('v', $value) ? $value['v'] : $default;
    }

    public static function put(string $key, mixed $value, string $group = 'general', string $type = 'string'): self
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => ['v' => $value], 'group' => $group, 'type' => $type],
        );
    }

    /** Alias kept for readability at call sites: Setting::set(...) is put(). */
    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): self
    {
        return static::put($key, $value, $group, $type);
    }

    /** @return array<string, mixed> */
    public static function all_(): array
    {
        return static::query()->get()->mapWithKeys(fn ($s) => [$s->key => $s->value['v'] ?? null])->all();
    }
}
