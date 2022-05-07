<?php

namespace App\Http\Resources\Api\Admin\Allcategory;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\Admin\Allcategory\SpecificParentApiResource;

class SpecificAllcategoryApiResource extends JsonResource
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
            'description'        => $this->description,
            'name_en'            => $this->name_en,
            'allcategory_id'    => $this->allcategory_id,
            'need_attributes' => $this->need_attributes,
            'navbar' => $this->navbar,
            "parent"            => new SpecificParentApiResource($this->parent),
            'description_en'     => $this->description_en,     
            "media"              => $this->media,  
            "photo"              => $this->photo, 
            'created_at'         => $this->created_at,
        ];

    }
}