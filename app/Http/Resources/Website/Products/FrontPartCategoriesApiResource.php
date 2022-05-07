<?php

namespace App\Http\Resources\Website\Products;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class FrontPartCategoriesApiResource extends JsonResource
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
            'id'             => $this->id,
            'category_name'  => $this->category_name,
            'created_at'     => $this->created_at,
            'photo'          => $this->photo,
        ];
    }
}
