<?php

namespace App\Http\Resources\Api\V1\Admin\Countries;

use Illuminate\Http\Resources\Json\JsonResource;

class CountriesApiResource extends JsonResource
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
            'country_name'    => $this->country_name,
            'name_en'    => $this->name_en,
            'country_code'    => $this->country_code,
            'nicename'    => $this->nicename,
            'iso3'    => $this->iso3,
            'numcode'    => $this->numcode,
            'phonecode'    => $this->phonecode,
            'lang'    => $this->lang,
            'status'    => $this->status,
            'created_at'    => $this->created_at,
        ];
    }
}
