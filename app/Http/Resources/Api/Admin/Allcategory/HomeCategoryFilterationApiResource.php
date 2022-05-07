<?php

namespace App\Http\Resources\Api\Admin\Allcategory;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\Admin\Allcategory\CategoryGetItemsApiResource;
use App\Http\Resources\Admin\Manufacturer\ManufacturerApiResource;
use App\Http\Resources\Admin\OriginCountry\OriginCountryApiResource;

class HomeCategoryFilterationApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        return[
            'id'                 => $this->id,
            'name'               => $this->name,
           // 'description'        => $this->description,
            'name_en'            => $this->name_en,
            'allcategory_id'        => $this->allcategory_id,
            'level'   => $this->allcategories->count(),
            'need_attributes' => $this->need_attributes,
            'navbar' => $this->navbar,
            // new
            'sequence' => $this->got_seq,
            'commercial_seq' => $this->commercial_seq,
            
            'belongs_car_navbar' => $this->car_navbar == null ? 0 : 1,
            'belongs_commercial_navbar' => $this->commercial_navbar == null ? 0 : 1,
            
            'catName' => $this->allcategory_id == null ? null : $this->catName($this->allcategory_id),
        
            'categories' => CategoryGetItemsApiResource::collection($this->cats),

            'manufacturers' => ManufacturerApiResource::collection($this->manufacturers),
            
            'origins'      => OriginCountryApiResource::collection($this->origins),

            "media"              => $this->media,  
            "photo"              => $this->photo,
            'created_at'         => $this->created_at,
        ];

    }
}