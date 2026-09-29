<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelNode extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'model_nodes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commission_model_id',
        'name',
        'parent_id',
        'node_type',
        'personal_sale',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_sale' => 'decimal:2',
        ];
    }

    /**
     * Commission model this node belongs to.
     */
    public function commissionModel(): BelongsTo
    {
        return $this->belongsTo(CommissionModel::class, 'commission_model_id');
    }

    /**
     * Parent node in the tree hierarchy.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Direct children nodes in the tree hierarchy.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Outgoing edges where this node is the parent.
     */
    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(ModelEdge::class, 'parent_node_id');
    }

    /**
     * Incoming edge where this node is the child.
     */
    public function incomingEdge(): HasMany
    {
        return $this->hasMany(ModelEdge::class, 'child_node_id');
    }

    /**
     * Ledger entries where this node made the sale.
     */
    public function salesMadeLedger(): HasMany
    {
        return $this->hasMany(CommissionLedger::class, 'sale_node_id');
    }

    /**
     * Ledger entries where this node earned commission overrides.
     */
    public function commissionsEarnedLedger(): HasMany
    {
        return $this->hasMany(CommissionLedger::class, 'earner_node_id');
    }
}
