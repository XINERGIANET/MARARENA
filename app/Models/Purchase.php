<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Purchase
 * 
 * @property int $id
 * @property int|null $voucher_type
 * @property string|null $invoice_number
 * @property int $payment_method_id
 * @property int|null $supplier_id
 * @property bool $deleted
 * @property Carbon $date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property PaymentMethod $payment_method
 * @property Supplier|null $supplier
 * @property Collection|Discharge[] $discharges
 * @property Collection|PurchaseDetail[] $purchase_details
 *
 * @package App\Models
 */
class Purchase extends Model
{

	protected $fillable = [
		'voucher_type',
		'invoice_number',
		'payment_method_id',
		'supplier_id',
		'deleted',
		'date'
	];

	protected $appends = ['total'];

	protected $dates = [
		'date'
	];

	public function payment_method()
	{
		return $this->belongsTo(PaymentMethod::class);
	}

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function discharges()
	{
		return $this->hasMany(Discharge::class);
	}

	public function purchase_details()
	{
		return $this->hasMany(PurchaseDetail::class);
	}

	//Campo total
	public function getTotalAttribute()
	{
		return $this->purchase_details()->sum('subtotal');
	}
}
