<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Auth;
use App\Models\AddVendor;
use App\Models\Vendorstaff;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'orders';

    protected $appends = [
        'sumTotal', 'orderStatus', 'leftApproval', 'currentStatus',
    ];

    protected $fillable = [
    	'user_id', 'status', 'paid', 'approved', 'expired', 'order_number', 'order_total', 'shipping_address_id', 'payment_id', 'checkout_time',
    ];

    public function orderDetails()
    {
        return $this->hasMany(Orderdetail::class, 'order_id', 'id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'order_id', 'id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'order_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paymentway()
    {
        return $this->belongsTo(Paymentway::class, 'payment_id');
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_address_id');
    }

    public function getorderStatusAttribute()
    {
        $value = $this->status;
        if ($this->expired == 1) 
        {
            return 'cancelled due to expiration';
        }
        else{
            switch ($value) {
                case 'pending':
                return 'pending';
                break;
                case 'in progress':
                return 'in progress';
                break;
                case 'delivered':
                return 'delivered';
                break;
                case 'cancelled':
                return 'cancelled';
                break;
                default;
                return 'not';
            }
        }
    }

    public function getleftApprovalAttribute()
    {
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) {
                $left = $this->orderDetails->where('approved', 0)->where('producttype_id', 2)->count();
            } // end admin
           if (in_array('Vendor', $user_roles)){
            $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id     = $vendor->id;
          
            $left = $this->orderDetails->where('vendor_id', $vendor_id)
                    ->where('approved', 0)->where('producttype_id', 1)->count();
           } // end vendor
            if (in_array('Manager', $user_roles)){
            $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
            $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
            $vendor_id     = $vendor->id;
          
            $left = $this->orderDetails->where('vendor_id', $vendor_id)
                    ->where('approved', 0)->where('producttype_id', 1)->count();
           } // end vendor
                 if ($this->expired != 1 && $left >= 1) {
                    $left_approval = 1;
                }
                elseif ($this->expired != 1 && $left < 1) {
                    $left_approval = 0;
                }
                elseif ($this->expired == 1 && $left >= 1) {
                    $left_approval = 0;
                }
                elseif ($this->expired == 1 && $left < 1) {
                    $left_approval = 0;
                }
                else {
                    $left_approval = 0;
                }
                return $left_approval;
        } // end auth 
    }

    public function getcurrentStatusAttribute()
    {
         if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) {
                $left = $this->orderDetails->where('producttype_id', 2)->first();
                if ($left == null) {
                    $leftt = 'not wholesale'; 
                }else{
                    $leftt = $left->approved;
                }
            } // end admin
           if (in_array('Vendor', $user_roles))
           {
                $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
                $vendor_id     = $vendor->id;
                $left = $this->orderDetails->where('vendor_id', $vendor_id)
                                           ->where('producttype_id', 1)->first();
            if ($left == null)  // start left 
            {
                   // $leftt = 'admin control'; 
                    $lefttadmin = $this->orderDetails->where('producttype_id', 2)->first();
                    if ($lefttadmin == null) // etart lefttadmin
                    {
                        $arr = $this->orderDetails->pluck('approved')->toArray();
                        if(in_array(1, $arr)){
                                $leftt = 1;
                        }
                        else{ // else ahmed
                            if( in_array(0, $arr) && !in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            elseif(!in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 2;
                            }
                            elseif(!in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 3;
                            }
                            elseif( in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            elseif( in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            elseif( !in_array(0, $arr) && in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 2;
                            }
                            else{
                                $leftt = 0;
                            }
                        } // end else ahmed
                    }else{  // end lefttadmin
                            $leftt = $lefttadmin->approved;
                    }
            }else{  // end left 
                    $leftt = $left->approved;
            }
        } // end vendor
           if (in_array('User', $user_roles))  // start user
           {
                $arr = $this->orderDetails->pluck('approved')->toArray();
                if(in_array(1, $arr)){
                                $leftt = 1;
                }
                else{ // else ahmed
                            if( in_array(0, $arr) && !in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            if(!in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 2;
                            }
                            if(!in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 3;
                            }
                            if( in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            if( in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            if( !in_array(0, $arr) && in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 2;
                            }
                } // end else ahmed
           } // end user
           if (in_array('Manager', $user_roles))
           {
            $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
            $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
            $vendor_id     = $vendor->id;
    
                $left = $this->orderDetails->where('vendor_id', $vendor_id)
                                           ->where('producttype_id', 1)->first();
                if ($left == null)  // start left 
                {
                   // $leftt = 'admin control'; 
                    $lefttadmin = $this->orderDetails->where('producttype_id', 2)->first();
                    if ($lefttadmin == null) // etart lefttadmin
                    {
                        $arr = $this->orderDetails->pluck('approved')->toArray();
                        if(in_array(1, $arr)){
                                $leftt = 1;
                        }
                        else{ // else ahmed
                            if( in_array(0, $arr) && !in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            if(!in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 2;
                            }
                            if(!in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 3;
                            }
                            if( in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            if( in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            if( !in_array(0, $arr) && in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 2;
                            }
                        } // end else ahmed
                    }else{  // end lefttadmin
                            $leftt = $lefttadmin->approved;
                    }
            }else{  // end left 
                    $leftt = $left->approved;
            }
           } // end manager
                 if ($leftt == 1) {
                    $left_approval = 'in progress';
                }
                elseif ($leftt == 2) {
                    $left_approval = 'cancelled';
                }
                elseif ($leftt == 3) {
                    $left_approval = 'expired';
                }
                elseif ($leftt == 0) {
                    $left_approval = 'pending';
                }
                elseif ($leftt == 4) {
                    $left_approval = 'new';
                }
               /* elseif ($leftt == 'admin control') {
                    $left_approval = 'admin control';
                }*/
                else{
                    $left_approval = 'tracking';
                }
                return $left_approval;
        } // end auth   
    } 

    public function getsumTotalAttribute()
    {
        $sum = $this->orderDetails->sum('total');
        return $sum;
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

