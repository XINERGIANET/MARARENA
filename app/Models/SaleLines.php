<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleLines extends Model
{
    use HasFactory;

        /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sale_lines';

    protected $fillable = [
        'name',
        'deleted',
    ];

    public function products()
    {
    return $this->hasMany(Product::class);
    }
}
