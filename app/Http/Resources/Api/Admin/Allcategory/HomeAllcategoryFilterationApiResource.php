<?php

namespace App\Http\Resources\Api\Admin\Allcategory;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\Admin\Allcategory\CategoryGetItemsApiResource;

class HomeAllcategoryFilterationApiResource extends JsonResource
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
            'name_en'            => $this->name_en,
            'allcategory_id'        => $this->allcategory_id,
           // 'level'   => $this->allcategories->count(),
             'need_attributes' => $this->need_attributes,

             'count_cats' => $this->count_cats,
            // 'navbar' => $this->navbar,

              // new
           // 'sequence' => $this->sequence,
           // 'belongs_car_navbar' => $this->car_navbar == null ? 0 : 1,
           // 'belongs_commercial_navbar' => $this->commercial_navbar == null ? 0 : 1,
            // new

           // 'catName' => $this->allcategory_id == null ? null : $this->catName($this->allcategory_id),
           // 'categories' => $this->cats == null ? null : CategoryGetItemsApiResource::collection($this->cats),
           // "media"              => $this->media,  
          //  "photo"              => $this->photo,
            'created_at'         => $this->created_at,
        ];

    }
}