<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CarModelResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    	/*return [
    		"id"             => $this->id,
            "carmodel"       => $this->carmodel,
            "created_at"     => $this->created_at,
            "updated_at"     => $this->updated_at,
            "deleted_at"     => $this->deleted_at,
            "carmade_id"     => $this->carmade_id,
            "carmadeName"    => $this->carmade->car_made,
            "carmade"        => $this->carmade,
    	];*/
    }
}
