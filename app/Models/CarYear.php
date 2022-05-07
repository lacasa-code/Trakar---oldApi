<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeInterface;

class CarYear extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'car_years';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'year',
        'lang',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function yearProducts()
    {
        return $this->hasMany(Product::class, 'year_id', 'id');
    }

    /*public function products()
    {
        return $this->belongsToMany(Product::class);
    }*/
}
