<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OverrideSale extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'override_sales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commission_model_id',
        'salesperson_name',
        'amount',
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
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the commission model that owns this sale.
     */
    public function commissionModel(): BelongsTo
    {
        return $this->belongsTo(CommissionModel::class, 'commission_model_id');
    }
}
