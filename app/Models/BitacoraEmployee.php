<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'bitacora_id',
        'bitacora_activity_id',
        'employee_id',
        'is_absent',
        'date',
        'hours_worked',
        'overtime_hours',
        'base_rate_applied',
        'overtime_rate_applied',
        'total_earned',
    ];

    protected function casts(): array
    {
        return [
            'is_absent' => 'boolean',
            'date' => 'date:Y-m-d',
            'hours_worked' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'base_rate_applied' => 'decimal:2',
            'overtime_rate_applied' => 'decimal:2',
            'total_earned' => 'decimal:2',
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
