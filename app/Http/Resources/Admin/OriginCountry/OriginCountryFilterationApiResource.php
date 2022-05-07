<?php

namespace App\Http\Resources\Admin\OriginCountry;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class OriginCountryFilterationApiResource extends JsonResource
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
            'id'                  => $this->id,
            'country_name'        => $this->country_name,
            'name_en'    => $this->name_en,
            'count_origins' => $this->count_origins,
            'country_code'        => $this->country_code,
            'created_at'          => $this->created_at,
            'time_created'        => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                        ->format('Y-m-d H:i:s'),
           // 'status'              => $this->status,
        ];
    }
}
