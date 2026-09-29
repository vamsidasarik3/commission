<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OverrideCommission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'override_commissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commission_model_id',
        'seller_name',
        'sale_amount',
        'recipient_name',
        'child_name',
        'generation',
        'rate',
        'commission_amount',
        'is_eligible',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission_model_id' => 'integer',
            'sale_amount' => 'decimal:2',
            'generation' => 'integer',
            'rate' => 'decimal:4',
            'commission_amount' => 'decimal:2',
            'is_eligible' => 'boolean',
        ];
    }

    /**
     * Get the commission model that owns this commission item.
     */
    public function commissionModel(): BelongsTo
    {
        return $this->belongsTo(CommissionModel::class, 'commission_model_id');
    }
}
