<?php

namespace App\Http\Resources\Api\V1\Admin\MainCategories;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\Admin\MainCategories\CategoryGetItsPartCategoriesApiResource;

class MainGetItsCategoriesApiResource extends JsonResource
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
        return [
            
            'id' => $this->id,
            'name' => $this->name,
            'name_en' => $this->name_en,
            'description' => $this->description,
            'last_level' => ( (count($this->part_categories) == 0 && count($this->products) > 0) ? 1 : 0),
            'part_categories' => CategoryGetItsPartCategoriesApiResource::collection($this->part_categories),
            'lang'    => $this->lang,
            'status'    => $this->status,
            'created_at'    => $this->created_at,
        ];
    }
}
