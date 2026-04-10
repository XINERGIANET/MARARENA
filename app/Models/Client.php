<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clients';

    protected $fillable = [
        'name', 
        'business_name',
        'commercial_name',
        'contact_name',
        'document',
        'phone',
        'address',
        'department',
        'province',
        'district',
        'deleted',
    ];

    public function sales()
	{
		return $this->hasMany(Sale::class);
	}
}
