<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelEdge extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'model_edges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commission_model_id',
        'parent_node_id',
        'child_node_id',
        'override_rate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'override_rate' => 'decimal:4',
        ];
    }

    /**
     * Commission model this edge belongs to.
     */
    public function commissionModel(): BelongsTo
    {
        return $this->belongsTo(CommissionModel::class, 'commission_model_id');
    }

    /**
     * Parent node in this relationship.
     */
    public function parentNode(): BelongsTo
    {
        return $this->belongsTo(ModelNode::class, 'parent_node_id');
    }

    /**
     * Child node in this relationship.
     */
    public function childNode(): BelongsTo
    {
        return $this->belongsTo(ModelNode::class, 'child_node_id');
    }
}
