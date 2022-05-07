<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeInterface;

class CarModel extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'car_models';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'carmade_id',
        'lang',
        'carmodel',
        'created_at',
        'updated_at',
        'deleted_at',
         // august 16 2021
        'name_en',
        'description_en',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function carModelProducts()
    {
        return $this->hasMany(Product::class, 'car_model_id', 'id');
    }

    /*public function products()
    {
        return $this->belongsToMany(Product::class);
    }*/

    public function carmade()
    {
        return $this->belongsTo(CarMade::class, 'carmade_id');
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
