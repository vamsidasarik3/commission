<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionLedger extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_ledger';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commission_model_id',
        'sale_node_id',
        'earner_node_id',
        'generation',
        'sale_amount',
        'rate_applied',
        'commission_amount',
        'is_eligible',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generation' => 'integer',
            'sale_amount' => 'decimal:2',
            'rate_applied' => 'decimal:4',
            'commission_amount' => 'decimal:2',
            'is_eligible' => 'boolean',
            'status' => 'string',
        ];
    }

    /**
     * Get the formatted status string ('paid' or 'NOT PAID').
     */
    public function getStatusAttribute(?string $value): string
    {
        if ($value !== null && $value !== '') {
            return $value;
        }

        return $this->is_eligible ? 'paid' : 'NOT PAID';
    }

    /**
     * Name of the seller node.
     */
    public function getSaleNodeNameAttribute(): ?string
    {
        return $this->saleNode?->name;
    }

    /**
     * Name of the earning upline node.
     */
    public function getEarnerNodeNameAttribute(): ?string
    {
        return $this->earnerNode?->name;
    }

    /**
     * Commission model this ledger entry belongs to.
     */
    public function commissionModel(): BelongsTo
    {
        return $this->belongsTo(CommissionModel::class, 'commission_model_id');
    }

    /**
     * Node that originated the sale.
     */
    public function saleNode(): BelongsTo
    {
        return $this->belongsTo(ModelNode::class, 'sale_node_id');
    }

    /**
     * Node that earned the override commission.
     */
    public function earnerNode(): BelongsTo
    {
        return $this->belongsTo(ModelNode::class, 'earner_node_id');
    }
}
