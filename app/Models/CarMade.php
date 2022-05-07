<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeInterface;
use Carbon\Carbon;

class CarMade extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'car_mades';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        // 'categoryid_id',
        'cartype_id',
        'car_made',
        'lang',
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

    public function carmadeCarModels()
    {
        return $this->hasMany(CarModel::class, 'carmade_id', 'id');
    }

    public function carMadeProducts()
    {
        return $this->hasMany(Product::class, 'car_made_id', 'id');
    }

    /*public function categoryid()
    {
        return $this->belongsTo(ProductCategory::class, 'categoryid_id');
    }*/

    public function car_type()
    {
        return $this->belongsTo(Allcategory::class, 'cartype_id');
    }

   /* public function getCreatedAtAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y-m-d H:i:s');
    }*/

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
