<?php

namespace App\Http\Resources\Api\V1\Admin\Areas;

use Illuminate\Http\Resources\Json\JsonResource;

class SingleAreasApiResource extends JsonResource
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
            'area_name' => $this->area_name,
            'name_en'    => $this->name_en,
            'country_id' => $this->country_id,
            'country_name' => $this->country->country_name,
            'lang' => $this->lang,
            'status' => $this->status,
            'created_at'  => $this->created_at,

        ];
    }
}
