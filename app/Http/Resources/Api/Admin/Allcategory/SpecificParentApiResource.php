<?php

namespace App\Http\Resources\Api\Admin\Allcategory;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\Admin\Allcategory\SpecificParentApiResource;

class SpecificParentApiResource extends JsonResource
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
        return [
//
            "id"               => $this->id,
            "name"             => $this->name,
            "name_en"          => $this->name_en,
            'need_attributes' => $this->need_attributes,
            "parent"           => array(new SpecificParentApiResource($this->parent)),
            "media"            => $this->media,
            "photo"            => $this->photo,
        ];
    }
}
