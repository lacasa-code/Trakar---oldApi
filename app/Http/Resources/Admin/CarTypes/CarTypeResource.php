<?php

namespace App\Http\Resources\Admin\CarTypes;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CarTypeResource extends JsonResource
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
            'id'                   => $this->id,
            'type_name'            => $this->type_name,
            'name_en'              => $this->name_en,
            'description_en'       => $this->description_en,
            'lang'                 => $this->lang,
            'photo'                => $this->photo,
           'created_at'            => $this->created_at,
           'time_created'          => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                      ->format('Y-m-d H:i:s'),
        ];
    }
}
