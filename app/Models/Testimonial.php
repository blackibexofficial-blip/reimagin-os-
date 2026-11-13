<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['author', 'role', 'company', 'quote', 'rating', 'avatar_path', 'is_published', 'position'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'rating' => 'integer'];
    }
}
