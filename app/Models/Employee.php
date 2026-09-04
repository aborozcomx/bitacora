<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $branch_id
 * @property string $first_name
 * @property string $last_name
 * @property string $employee_code
 * @property float $base_hourly_rate
 * @property float $overtime_hourly_rate
 * @property bool $is_active
 * @property Branch|null $branch
 */
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'first_name',
        'last_name',
        'employee_code',
        'base_hourly_rate',
        'overtime_hourly_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_hourly_rate' => 'decimal:2',
            'overtime_hourly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return HasMany<BitacoraEmployee, $this>
     */
    public function bitacoraEntries(): HasMany
    {
        return $this->hasMany(BitacoraEmployee::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
