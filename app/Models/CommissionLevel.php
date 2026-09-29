<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionLevel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_levels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commission_model_id',
        'level',
        'commission_rate',
        'main_person',
        'main_sales',
        'main_commission',
        'side_person',
        'side_sales',
        'side_commission',
        'selected_commission',
        'leader_commission',
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
            'level' => 'integer',
            'commission_rate' => 'decimal:4',
            'main_sales' => 'decimal:2',
            'main_commission' => 'decimal:2',
            'side_sales' => 'decimal:2',
            'side_commission' => 'decimal:2',
            'selected_commission' => 'decimal:2',
            'leader_commission' => 'decimal:2',
        ];
    }

    /**
     * Get the commission model that owns this level.
     */
    public function commissionModel(): BelongsTo
    {
        return $this->belongsTo(CommissionModel::class, 'commission_model_id');
    }
}
