<?php

namespace App\Http\Resources\Api\Admin\Allcategory;

use Illuminate\Http\Resources\Json\JsonResource;

class SiblingsApiResource extends JsonResource
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
           // 'level'   => $this->allcategories->count(),
           // 'current_level'   => $this->parent == null ? null : (in_array($this->parent->id, [1, 2, 3]) ? 'type' : 'notype'),
         //   'catName' => $this->allcategory_id == null ? null : $this->catName($this->allcategory_id),
            //'description_en'     => $this->description_en,   
          //  'categories' => $this->allcategories == null ? null : CategoryGetItemsApiResource::collection($this->allcategories),
            "media"              => $this->media,  
            "photo"              => $this->photo,
            'created_at'         => $this->created_at,
        ];

    }
}