<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'stores';

    /* protected $appends = [
        'vendorName',
    ]; */

    protected $fillable = [
    	'name',
        'lang',
    	'address', 
    	'lat', 
    	'long', 
    	'vendor_id', 
    	'moderator_name', 
    	'moderator_phone', 
    	'moderator_alt_phone', 
        'head_center',
    	'status', 
        // june 16 2021
        'country_id',
        'area_id',
        'city_id',
        'serial_id',
    ];

    public function vendor()
    {
        return $this->belongsTo(AddVendor::class, 'vendor_id');
    }

    public function vendorstaff()
    {
        return $this->belongsToMany(Vendorstaff::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    
    /*public function getVendorNameAttribute()
    {
        return $this->vendor->vendor_name; // (AddVendor::class, 'vendor_id');
    }*/

   /* public function products()
    {
        return $this->belongsToMany(Product::class);//, 'store_id', 'id');
    }*/

    public function products()
    {
        return $this->hasMany(Product::class, 'store_id', 'id');
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

