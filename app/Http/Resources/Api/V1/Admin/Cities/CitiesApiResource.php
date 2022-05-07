<?php

namespace App\Http\Resources\Api\V1\Admin\Cities;

use Illuminate\Http\Resources\Json\JsonResource;

class CitiesApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'city_name' => $this->city_name,
            'name_en'    => $this->name_en,
            'area_id' => $this->area_id,
            'area_name' => $this->area->area_name,
            'country_name' => $this->area->country->country_name,
            'lang' => $this->lang,
            'status' => $this->status,
            'created_at'  => $this->created_at,
        ];
    }
}
