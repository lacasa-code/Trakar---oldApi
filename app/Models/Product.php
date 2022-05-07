<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use \DateTimeInterface;

class Product extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    public $table = 'products';

    protected $appends = [
        'photo',
    ];

    protected $casts = ['allcategory_id_arr' => 'array'];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'car_made_id',
        'lang',
        'car_model_id',
        'year_id',
        'part_category_id',
        'category_id',
        'cartype_id',
        'vendor_id',
        'name',
        'description',
        'discount',
        'price',
        'store_id',
        'quantity',
        'serial_number', 
        'manufacturer_id', 
        'prodcountry_id', 
        'transmission_id',
        'producttype_id',
        'serial_coding',
        'holesale_price',
        'no_of_orders',
        // added new
        'original_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'default_media',
        //  june 18 2021
        'year_from',
        'year_to',
        'serial_id',
        'maincategory_id',
        // june 21 2021
        'approved',
        'qty_reminder',
        'actual_price',

        // august 16 2021
        'name_en',
        'description_en',
        'allcategory_id',
        'allcategory_id_arr',
        'size',
        'width',
        'height',
    ];

 /*   public function normal_price()
    {
        $price = $this->product_prices->where('producttype_id', 1)->sum('price');
        $discount   = $this->discount;
        $percentage =  $price * ($discount / 100);
        $PriceAfterDiscount = $price - $percentage;
        return $PriceAfterDiscount;
    }

    public function wholesale_price()
    {
        return $this->product_prices->where('producttype_id', 2)->sum('price');
    }
*/
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

   /* public function categories()
    {
        return $this->belongsToMany(ProductCategory::class);
    }*/

     public function tags()
    {
        return $this->belongsToMany(ProductTag::class);
    }

    public function main_category()
    {
        return $this->belongsTo(Maincategory::class, 'maincategory_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

   /* public function allcategory()
    {
        return $this->belongsTo(Allcategory::class, 'allcategory_id');
    } */

    public function car_type()
    {
        return $this->belongsTo(Cartype::class, 'cartype_id');
    }

    public function car_made()
    {
        return $this->belongsTo(CarMade::class, 'car_made_id');
    }

    public function product_type()
    {
        return $this->belongsTo(Producttype::class, 'producttype_id');
    }

    /*public function car_model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }*/

    /*public function year()
    {
        return null; // $this->belongsTo(CarYear::class, 'year_id');
    }*/

    public function year_from_func()
    {
        return $this->belongsTo(CarYear::class, 'year_from');
        //$from_year = CarYear::where('id', $this->year_from)->first();
        //return $from_year;
    }

    public function year_to_func()
    {
        return $this->belongsTo(CarYear::class, 'year_to');
        // $to_year = CarYear::where('id', $this->year_to)->first();
        // return $to_year;
    }

    public function car_model()
    {
        return $this->belongsToMany(CarModel::class);
    }

    public function allcategory()
    {
        return $this->belongsToMany(Allcategory::class);
    }

    /*public function year()
    {
        return $this->belongsToMany(CarYear::class);
    }*/


    public function orderDetails()
    {
        return $this->hasMany(Orderdetail::class, 'product_id', 'id');
    }

   /* public function product_prices()
    {
        return $this->hasMany(Productprice::class, 'product_id', 'id');
    }*/

     public function views()
    {
        return $this->hasMany(Productview::class, 'product_id', 'id');
    }

    public function part_category()
    {
        return $this->belongsTo(PartCategory::class, 'part_category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(AddVendor::class, 'vendor_id');
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function transmission()
    {
        return $this->belongsTo(Transmission::class, 'transmission_id');
    }

    public function origin_country()
    {
        return $this->belongsTo(Prodcountry::class, 'prodcountry_id');
    }

   /* public function store()
    {
        return $this->belongsToMany(Store::class);
    }*/

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function productreviews()
    {
        return $this->hasMany(Productreview::class, 'product_id', 'id');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluationproduct::class, 'product_id', 'id');
    }

    public function sumEvaluations(){
        return $this->evaluations()->avg('evaluation_value');
    }

    public function getPhotoAttribute()
    {
        $files = $this->getMedia('photo');
        
        $files->each(function ($item) {
            $env   = env('Space_URL');
            $item->image     = $env.'/products/'.$item['file_name'];
            $item->default   = $this->default_media == $item->id ? 1 : 0;
            $item->url       = $item->getUrl();
            $item->fullurl   = $item->getFullUrl();
            $item->thumbnail = $item->getUrl('thumb');
            $item->preview   = $item->getUrl('preview');
        });

        return $files;
    }

    public function PriceAfterDiscount()
    {
        $price      = $this->price;
        $discount   = $this->discount;
        $percentage =  $price * ($discount / 100);
        $PriceAfterDiscount = $price - $percentage;
        return $PriceAfterDiscount;
    }
}
