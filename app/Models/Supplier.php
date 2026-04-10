<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Supplier
 * 
 * @property int $id
 * @property string $company_name
 * @property string $document
 * @property string|null $commercial_name
 * @property string|null $phone
 * @property string $deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Purchase[] $purchases
 *
 * @package App\Models
 */
class Supplier extends Model
{
	protected $fillable = [
		'company_name',
		'document',
		'commercial_name',
		'phone',
		'deleted'
	];

	public function purchases()
	{
		return $this->hasMany(Purchase::class);
	}
}
