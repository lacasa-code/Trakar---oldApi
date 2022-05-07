<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class SpecificAddVendorApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
      /*  return [
               "id"            => $this->id,
                "vendor_name"  => $this->vendor_name,
                "email"        => $this->email,
                "type"         => $this->type,
                "serial"       => $this->serial,
                "created_at"   => $this->created_at,
                "updated_at"   => $this->updated_at,
                "deleted_at"   => $this->deleted_at,
                "userid_id"    => $this->userid_id,
                "images"       => $this->images,
                "userid"       => $this->userid,
                "media"        => $this->media,
        ];*/
    }
}
