<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'bitacora_id',
        'bitacora_activity_id',
        'concept',
        'amount',
        'date',
        'payment_method_id',
        'card_type_id',
        'payment_card_id',
        'reference_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
        ];
    }

    public function bitacora(): BelongsTo
    {
        return $this->belongsTo(Bitacora::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(BitacoraActivity::class, 'bitacora_activity_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class);
    }

    public function paymentCard(): BelongsTo
    {
        return $this->belongsTo(PaymentCard::class);
    }
}
