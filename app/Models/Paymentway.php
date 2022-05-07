<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Paymentway extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'paymentways';

    protected $fillable = [
    	'payment_name', 'status', 'lang',
    ];
}
