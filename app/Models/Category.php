<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'sale_line_id',
        'deleted',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    public function sale_line()
    {
        return $this->belongsTo(SaleLines::class);
    }
}
