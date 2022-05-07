<?php

namespace App\Http\Resources\Api\Admin\Allcategory;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\Admin\Allcategory\CategoryGetItemsApiResource;

class HomeAllcategorySpecificApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
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
            // new
           // 'current_level'   => $this->parent == null ? null : (in_array($this->parent->id, [1, 2, 3]) ? 'type' : 'notype'),
            'catName' => $this->allcategory_id == null ? null : $this->catName($this->allcategory_id),
            //'description_en'     => $this->description_en,   
            'categories' => $this->allcategories == null ? null : CategoryGetItemsApiResource::collection($this->allcategories),
            "media"              => $this->media,  
            "photo"              => $this->photo,
            'created_at'         => $this->created_at,
        ];

    }
}