<?php

namespace App\Http\Resources\Api\V1\Admin\MainCategories;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\Admin\MainCategories\MainGetItsCategoriesApiResource;

class SingleMaincategoryNestedApiResource extends JsonResource
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
            'main_category_name' => $this->main_category_name,
            'name_en' => $this->name_en,
            'categories' => MainGetItsCategoriesApiResource::collection($this->categories),
            'lang'    => $this->lang,
            'status'    => $this->status,
            'created_at'    => $this->created_at,
        ];
    }
}
