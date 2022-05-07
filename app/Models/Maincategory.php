<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeInterface;
use App\Traits\Auditable;

class Maincategory extends Model
{
    use SoftDeletes, Auditable, HasFactory;
    
    public $table = 'maincategories';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'main_category_name',
        'lang',
        'status',
         // august 16 2021
        'name_en',
        'description_en',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /*public function areas()
    {
        return $this->hasMany(Area::class);
    }*/

    public function categories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function Products()
    {
        return $this->hasMany(Product::class);
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
