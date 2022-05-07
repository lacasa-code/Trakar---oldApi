<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favouritecar extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'favouritecars';

    protected $fillable = [
    	'user_id',
    	'car_type_id',
        'car_made_id',
        'car_model_id',
        'car_year_id',
        'transmission_id',
    	'status', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function car_made()
    {
        return $this->belongsTo(CarMade::class, 'car_made_id');
    }

    public function car_model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

    public function car_year()
    {
        return $this->belongsTo(CarYear::class, 'car_year_id');
    }

    public function car_type()
    {
        return $this->belongsTo(Cartype::class, 'car_type_id');
    }

    public function transmission()
    {
        return $this->belongsTo(Transmission::class, 'transmission_id');
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
