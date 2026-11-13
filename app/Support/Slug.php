<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class Slug
{
    /**
     * Build a unique slug inside the given table, ignoring the given record.
     */
    public static function unique(string $value, string $table, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $suffix = 2;

        while (
            DB::table($table)
                ->where($column, $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public static function forModel(Model $model, string $value): string
    {
        return self::unique($value, $model->getTable(), $model->getKey());
    }
}
