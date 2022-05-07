<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'invoices';

    protected $appends = [
        'vendorDetails',
    ];

    protected $fillable = [
    	'order_id', 'vendor_id', 'invoice_number', 'invoice_total', 'status', 
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function vendor()
    {
        return $this->belongsTo(AddVendor::class, 'vendor_id');
    }

    public function getvendorDetailsAttribute()
    {
        $vendor_details = $this->vendor->only(['vendor_name', 'email']);
        $this->makeHidden('vendor');
        return $vendor_details;
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

