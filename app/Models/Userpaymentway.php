<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Userpaymentway extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'userpaymentways';

    protected $fillable = [
    	'user_id', 'paymentway_id', 'status'
    ];
}
