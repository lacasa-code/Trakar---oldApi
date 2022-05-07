<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Propaganistas\LaravelPhone\PhoneNumber;

class Shipping extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'shippings';

    protected $fillable = [
    	'user_id', 
    	'status', 
        'lang',
    	'recipient_name', 
    	'recipient_phone', 
    	'recipient_alt_phone', 
    	'recipient_email', 
    	'address', 
    	'city', 
    	'state', 
    	'country_code', 
    	'postal_code', 
    	'latitude', 
    	'longitude',
        // added new 30may2021
        'last_name',
        'area',
        'district', 
        'home_no',
        'floor_no', 
        'apartment_no', 
        'telephone_no',
        'street',
        'nearest_milestone', 
        'notices',
        'default',
        'city_id',
        'area_id',
        'country_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function is_city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function is_area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
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

    /*public function getRecipientPhoneAttribute($value)
    {
        $item         = Country::where('id', $this->country_id)->first();
        $phone_number  = PhoneNumber::make($value, $item->country_code)->formatNational();


        //$len          = strlen($item->phonecode);
        // $phone_number = substr($value, $len);
        return $phone_number;
    }*/
}