<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\Vendor\Member\MemberApiResource;
use Propaganistas\LaravelPhone\PhoneNumber;

class StoreApiResource extends JsonResource
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
            "id"                   => $this->id,
            "name"                 => $this->name,
            "address"              => $this->address,
            "lat"                  => $this->lat,
            "long"                 => $this->long,
            "vendor_id"            => $this->vendor_id,
            // june 17 2021
            'country_id'           => $this->country_id,
            'phonecode'         => $this->country_id == null ? null : $this->country->phonecode,
            'country_code'      => $this->country_id == null ? null : $this->country->country_code,
            'phone_number'      => PhoneNumber::make($this->moderator_phone, $this->country->country_code)->formatNational(),
            'area_id'              => $this->area_id,
            'city_id'              => $this->city_id,
            'country_name'  => $this->country_id == null ? null : $this->country->country_name,
            'area_name'     => $this->area_id == null ? null : $this->area->area_name,
            'city_name'     => $this->city_id == null ? null : $this->city->city_name,
            'head_center'   => $this->head_center == null ? null : $this->head_center,
            'serial_id'     => $this->serial_id == null ? null : $this->serial_id,
            // june 17 2021
           // "moderator_name"       => $this->moderator_name,
            "moderator_phone"      => $this->moderator_phone,
            "moderator_alt_phone"  => $this->moderator_alt_phone,
            "status"               => $this->status,
            "created_at"           => $this->created_at,
            "updated_at"           => $this->updated_at,
            "deleted_at"           => $this->deleted_at,
            "vendor_name"          => $this->vendor->vendor_name,
            "vendor"               => $this->vendor,
            "members"              => $this->vendorstaff->count() <= 0 ? null : MemberApiResource::collection($this->vendorstaff),
        ];
    }
}
