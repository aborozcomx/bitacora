<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'requires_card_details',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requires_card_details' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function paymentCards(): HasMany
    {
        return $this->hasMany(PaymentCard::class);
    }
}
