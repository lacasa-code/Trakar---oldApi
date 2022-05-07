<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeInterface;

class Adpositions extends Model
{
    use HasFactory, SoftDeletes;

     public $table = 'adpositions';

    protected $fillable = [
    	'position_name',
    	'lang',
    	'status', 
    ];

    public function ads()
    {
        return $this->hasMany(Advertisement::class, 'ad_position', 'id');
    }
}