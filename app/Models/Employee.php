<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employees';

    protected $fillable = [
        'name',
        'last_name',
        'document',
        'birth_date',
        'phone',
        'address',
        'pin',
        'deleted',
    ];

    protected $dates = [
        'birth_date',
    ];

}
