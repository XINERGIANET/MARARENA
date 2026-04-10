<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sale_details';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',   
        'subtotal',
        'deleted',
    ];

    public function sale()
	{
		return $this->belongsTo(Sale::class);
	}

    public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
