<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OverrideRelationship extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'override_relationships';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commission_model_id',
        'parent_name',
        'child_name',
        'rate',
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
            'rate' => 'decimal:4',
        ];
    }

    /**
     * Get the commission model that owns this relationship edge.
     */
    public function commissionModel(): BelongsTo
    {
        return $this->belongsTo(CommissionModel::class, 'commission_model_id');
    }
}
