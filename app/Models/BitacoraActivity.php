<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BitacoraActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'bitacora_id',
        'activity_type_id',
        'date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    public function bitacora(): BelongsTo
    {
        return $this->belongsTo(Bitacora::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(BitacoraEmployee::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(BitacoraExpense::class);
    }
}
