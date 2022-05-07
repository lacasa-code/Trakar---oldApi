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

class ProductCategory extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    protected $appends = [
        'photo',
    ];

    public $table = 'product_categories';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'lang',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
        // june 19 2021
        'maincategory_id',
         // august 16 2021
        'name_en',
        'description_en',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /*public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }*/

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->setManipulations(['w' => 50, 'h' => 50])
             ->performOnCollections('photo')
             ->nonQueued();

        $this->addMediaConversion('small')
             ->setManipulations(['w' => 280, 'h' => 210])
             ->performOnCollections('photo')
             ->nonQueued();

        $this->addMediaConversion('medium')
             ->setManipulations(['w' => 400, 'h' => 300])
             ->performOnCollections('photo')
             ->nonQueued();

        $this->addMediaConversion('large')
             ->setManipulations(['w' => 640, 'h' => 480])
             ->performOnCollections('photo')
             ->nonQueued();

         $this->addMediaConversion('preview')
         ->setManipulations(['w' => 120, 'h' => 120])
         ->performOnCollections('photo')
         ->nonQueued();
    }
    // end registerConversions

    public function main_category()
    {
        return $this->belongsTo(Maincategory::class, 'maincategory_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    /*public function categoryidCarMades()
    {
        return $this->hasMany(CarMade::class, 'categoryid_id', 'id');
    }*/

    public function part_categories()
    {
        return $this->hasMany(PartCategory::class, 'category_id', 'id');
    }

    public function categoryidproducts()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function getPhotoAttribute()
    {
        $file = $this->getMedia('photo')->last();
        $env  = env('Space_URL');
        if ($file) {
            $file->image     = $env.'/product-categories/'.$file['file_name'];
            $file->url       = $file->getUrl();
            $file->fullurl       = $file->getFullUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }
}
