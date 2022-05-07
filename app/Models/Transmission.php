<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transmission extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'transmissions';

    protected $fillable = [
    	'transmission_name',
        'lang',
    	'status', 
        'name_en',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'transmission_id', 'id');
    }

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
       // return Carbon::createFromFormat('Y-m-d H:i:s', $value->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
       // return Carbon::createFromFormat('Y-m-d H:i:s', $value->format('Y-m-d H:i:s');
    }
}
