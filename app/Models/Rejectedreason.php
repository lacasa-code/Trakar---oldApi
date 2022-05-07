<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeInterface;
use App\Traits\Auditable;

class Rejectedreason extends Model
{
    use SoftDeletes, Auditable, HasFactory;
    
    public $table = 'rejectedreasons';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'field',
        'lang',
        'status',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function vendor()
    {
        return $this->belongsToMany(AddVendor::class)->withPivot('reason')->withTimestamps();
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
