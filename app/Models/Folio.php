<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'current_consecutive',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_consecutive' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
