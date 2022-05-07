<?php

namespace App\Http\Resources\Api\V1\Admin\MainCategories;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\Admin\MainCategories\PartGetItsProductsApiResource;

class CategoryGetItsPartCategoriesApiResource extends JsonResource
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
            'category_name' => $this->category_name,
            'name_en' => $this->name_en,
            'products' => PartGetItsProductsApiResource::collection($this->partCategoryProducts),
            'lang'    => $this->lang,
            'status'    => $this->status,
            'created_at'    => $this->created_at,
        ];
    }
}
