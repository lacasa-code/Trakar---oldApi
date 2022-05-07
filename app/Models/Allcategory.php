<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use \DateTimeInterface;
use App\Traits\Auditable;

class Allcategory extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    public $table = 'allcategories';

    protected $appends = [
        'photo',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'description',
        'allcategory_id',
        'name_en',
        'description_en',
        'need_attributes',
        'navbar',
        'sequence',
        'car_navbar',
        'commercial_navbar',
        'commercial_sequence',
    ];

    public function allcategories()
    {
        return $this->hasMany(Allcategory::class);
    }

    public function catName($id)
    {
        $name = Allcategory::where('id', $id)->first();
        if ($name == null) {
            $get_name = 'no parent';
        }else{
            $get_name = $name->name;
        }
        return $get_name;
    }

    public function getParentssAttribute()
    {
        $parents = collect([$this]);
        $parent = $this->parent;
        while(!is_null($parent)) {
            $parents->push($parent);
            $parent = $parent->parent;
        }
        return $parents;
    }

    public function childrenAllCategories()
    {
       return $this->hasMany(Allcategory::class)->with('allcategories');
    }

    public function commonOnes()
    {
        $id = $this->id;
        if ($this->allcategory_id != 7) {
            $cats = Allcategory::whereIn('allcategory_id', [$id, 7])->get();
        }else{
            $cats = Allcategory::whereIn('allcategory_id', [$id])->get();
        }
        
        if (count($cats) > 0) {
           $cats_get = $cats;
        }else{
            $cats_get = NULL;
        }
        return $cats_get;
    }

    public function getChildsAttribute()
    {
        $childsarr = array($this->id);
        $child = $this->childrenAllCategories;
        //return $child;
        while (count($child) != 0) {
            foreach ($child as $value) {
                array_push($childsarr, $value->id);
                $child = $value->childrenAllCategories;
            }
        }
        return $childsarr;
    }

    /*public function products()
    {
        return $this->hasMany(Product::class, 'allcategory_id', 'id');
    }*/

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function car_mades()
    {
        return $this->hasMany(CarMade::class, 'cartype_id', 'id');
    }


    public function parent()
    {
        return $this->belongsTo(Allcategory::class, 'allcategory_id')->with('parent');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function getPhotoAttribute()
    {
        $file = $this->getMedia('photo')->last();
        $env  = env('Space_URL');
        if ($file) {
            $file->image     = $env.'/all-categories/'.$file['file_name'];
            $file->url       = $file->getUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }
}
