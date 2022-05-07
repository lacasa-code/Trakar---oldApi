<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Helpcenter extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'helpcenters';

    protected $fillable = [
        'question', 'answer', 'created_by', 'status', 'lang',
    ];

    public function user()
    {
       return $this->belongsTo(User::class, 'created_by');
    }

    public function getCreatedAtAttribute($value)
    {
       return Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y-m-d H:i:s');
    }
}

