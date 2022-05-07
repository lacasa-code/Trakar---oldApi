<?php

namespace App\Http\Resources\Api\V1\Vendor;

use Illuminate\Http\Resources\Json\JsonResource;

class RejectReasonsApiResource extends JsonResource
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

            "id"          => $this->id,
            "field"       => $this->field,
            "lang"        => $this->lang,
            "rej_reason"  => $this->pivot['reason'],
            "created_at"  => \Carbon\Carbon::parse($this->pivot['created_at'])->format('Y-m-d H:i:s'),
            "updated_at"  => \Carbon\Carbon::parse($this->pivot['updated_at'])->format('Y-m-d H:i:s'),

        ];
    }
}
