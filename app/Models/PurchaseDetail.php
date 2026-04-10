<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseDetail
 * 
 * @property int $purchase_id
 * @property int $product_id
 * @property float $quantity
 * @property float $unit_price
 * @property float $subtotal
 * 
 * @property Product $product
 * @property Purchase $purchase
 *
 * @package App\Models
 */
class PurchaseDetail extends Model
{
	public $timestamps = false;

	protected $fillable = [
		'purchase_id',
		'product_id',
		'quantity',
		'unit_price',
		'subtotal'
	];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function purchase()
	{
		return $this->belongsTo(Purchase::class);
	}
}
