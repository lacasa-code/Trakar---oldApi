<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Cartype extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    public $table = 'cartypes';

    protected $appends = [
        'photo',
    ];


    protected $fillable = [
        'type_name',
       // 'description',
        'lang',
        'status',
         // august 16 2021
        'name_en',
        'description_en',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'cartype_id', 'id');
    }

    public function advertisements()
    {
        return $this->hasMany(Advertisement::class, 'cartype_id', 'id');
    }

    /* public function car_mades()
    {
        return $this->hasMany(CarMade::class, 'cartype_id', 'id');
    } */

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

    public function getPhotoAttribute()
    {
        $file = $this->getMedia('photo')->last();
        $env  = env('Space_URL');
        if ($file) {
            $file->image     = $env.'/car-types/'.$file['file_name'];
            $file->url       = $file->getUrl();
            $file->fullurl       = $file->getFullUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }
}
