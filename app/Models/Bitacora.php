<?php

namespace App\Models;

use Database\Factories\BitacoraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $branch_id
 * @property int $user_id
 * @property string $folio_number
 * @property string $date
 * @property string|null $notes
 * @property Branch|null $branch
 * @property User|null $user
 */
class Bitacora extends Model
{
    /** @use HasFactory<BitacoraFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'user_id',
        'client_id',
        'client_branch_id',
        'folio_number',
        'folio_prefix',
        'folio_consecutive',
        'date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<ClientBranch, $this>
     */
    public function clientBranch(): BelongsTo
    {
        return $this->belongsTo(ClientBranch::class);
    }

    /**
     * @return HasMany<BitacoraActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(BitacoraActivity::class);
    }

    /**
     * @return HasMany<BitacoraEmployee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(BitacoraEmployee::class);
    }

    /**
     * @return HasMany<BitacoraExpense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(BitacoraExpense::class);
    }

    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses->sum('amount');
    }

    public function getTotalPayrollAttribute(): float
    {
        return (float) $this->employees->sum('total_earned');
    }
}
