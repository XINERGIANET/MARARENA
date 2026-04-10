<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_methods';

    protected $fillable = [
        'name',
        'deleted',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
