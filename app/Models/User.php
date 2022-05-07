<?php

namespace App\Models;

use Carbon\Carbon;
use Hash;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use \DateTimeInterface;
//use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
// use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Auth\Passwords\canResetPassword;

class User extends Authenticatable
{
    use SoftDeletes, Notifiable, HasFactory, HasApiTokens, canResetPassword; //Authenticatable;

    public $table = 'users';

    protected $hidden = [
        'remember_token',
        'password',
    ];

    protected $dates = [
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'email',
        'lang',
        'email_verified_at',
        'password',
        'added_by_id',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
        // 31 may 2021
        'last_name', 
        'phone_no',
        'birthdate',
        'gender',
        //  6 june 2021
        'provider_id',
        'avatar',
        'facebook_id',
        'facebook_avatar',
        // july 25 2021
        'serial_id',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getIsAdminAttribute()
    {
        return $this->roles()->where('id', 1)->exists();
    }

   /* public function useridAddVendors()
    {
        return $this->hasMany(AddVendor::class, 'userid_id', 'id');
    }*/

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function vendor()
    {
        return $this->hasOne(AddVendor::class, 'userid_id', 'id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id', 'id');
    }

    public function revise_cart($id){
        $order = Order::where('user_id', $this->id)
                                ->whereNull('paid')
                                ->where('status', '!=', 4)
                                ->where('status', '!=', 2)
                                ->where('expired', 0)
                                ->whereDoesntHave('orderDetails', function($q){
                                    $q->where('approved', 1);
                                  })->first();
        if ($order == null) {
            return 0;
        }
        $orderdetails_ids = $order->orderDetails->pluck('product_id')->ToArray();
        if (in_array($id, $orderdetails_ids)) {
             return 1;
        }else{
            return 0;
        }
    }

    public function revise_wishlist($id){
        $exist = Wishlist::where('user_id', $this->id)->where('product_id', $id)->first();
        if ($exist == null) {
            return 0;
        }else{
            return 1;
        }
    }

    public function revise_favourites($id){
        $exist = Favouriteproduct::where('user_id', $this->id)->where('product_id', $id)
                                ->first();
        if ($exist == null) {
            return 0;
        }else{
            return 1;
        }
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['email_verified_at'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    public function productreviews()
    {
        return $this->hasMany(Productreview::class, 'user_id', 'id');
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Ticketcomment::class, 'user_id', 'id');
    }
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id', 'id');
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'id');
    }

    public function favourites()
    {
        return $this->hasMany(Favouriteproduct::class, 'user_id', 'id');
    }

    public function favouritecars()
    {
        return $this->hasMany(Favouritecar::class, 'user_id', 'id');
    }

    public function shippings()
    {
        return $this->hasMany(Shipping::class, 'user_id', 'id');
    }
}
