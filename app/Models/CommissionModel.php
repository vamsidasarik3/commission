<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_models';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'model_type',
        'description',
        'commission_rate',
        'number_of_levels',
        'max_generations',
        'total_sales',
        'total_potential_commission',
        'final_commission',
        'calculation_results',
        'weakest_person',
        'weakest_sales',
        'weakest_commission',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'model_type' => 'string',
            'max_generations' => 'integer',
            'commission_rate' => 'decimal:4',
            'number_of_levels' => 'integer',
            'total_sales' => 'decimal:2',
            'total_potential_commission' => 'decimal:2',
            'final_commission' => 'decimal:2',
            'calculation_results' => 'array',
            'weakest_sales' => 'decimal:2',
            'weakest_commission' => 'decimal:2',
        ];
    }

    /**
     * Determine if this model is an Override Commission Model (Model 2).
     */
    public function isOverrideModel(): bool
    {
        return $this->model_type === 'generation_override';
    }

    /**
     * Determine if this model is a Weakest Link Commission Model (Model 1).
     */
    public function isWeakestLinkModel(): bool
    {
        return $this->model_type === 'weakest_link' || empty($this->model_type);
    }

    /**
     * Determine if this model is a Unilevel MLM Commission Model (Model 3).
     */
    public function isUniLevelModel(): bool
    {
        return $this->model_type === 'unilevel';
    }

    /**
     * Get the formatted display name of the model type.
     */
    public function getModelTypeNameAttribute(): string
    {
        return match ($this->model_type) {
            'generation_override' => 'Level / Generation Override',
            'unilevel' => 'Unilevel MLM',
            default => 'Weakest Link',
        };
    }

    /**
     * Get the normalized nodes (persons/agents) in the model hierarchy.
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(ModelNode::class, 'commission_model_id');
    }

    /**
     * Get the normalized edges (directed override relationships) between nodes.
     */
    public function edges(): HasMany
    {
        return $this->hasMany(ModelEdge::class, 'commission_model_id');
    }

    /**
     * Get the normalized commission calculation ledger audit entries.
     */
    public function ledger(): HasMany
    {
        return $this->hasMany(CommissionLedger::class, 'commission_model_id')->orderBy('generation');
    }

    /**
     * Get the levels associated with the commission model (Model 1).
     */
    public function levels(): HasMany
    {
        return $this->hasMany(CommissionLevel::class, 'commission_model_id')->orderBy('level');
    }

    /**
     * Get the parent-child relationships/edges (Model 2).
     */
    public function relationships(): HasMany
    {
        return $this->hasMany(OverrideRelationship::class, 'commission_model_id');
    }

    /**
     * Get the recorded personal sales (Model 2).
     */
    public function overrideSales(): HasMany
    {
        return $this->hasMany(OverrideSale::class, 'commission_model_id');
    }

    /**
     * Get the calculated override commission payouts (Model 2).
     */
    public function overrideCommissions(): HasMany
    {
        return $this->hasMany(OverrideCommission::class, 'commission_model_id')->orderBy('generation');
    }
}
