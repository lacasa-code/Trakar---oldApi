<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Orderdetail extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'orderdetails';

    protected $fillable = [
    	'order_id', 
    	'product_id', 
    	'store_id', 
    	'vendor_id',
        'part_category_id',
        'producttype_id', 
        'vendor_type',
    	'quantity', 
    	'price', 
    	'discount', 
    	'total', 
        'approved', 
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function product_type()
    {
        return $this->belongsTo(Producttype::class, 'producttype_id');
    }

    public function partCategory()
    {
        return $this->belongsTo(Store::class, 'part_category_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function vendor()
    {
        return $this->belongsTo(AddVendor::class, 'vendor_id');
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
