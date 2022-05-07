<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\RoleResource;
use App\Models\Vendorstaff;

class SpecificUsersApiResource extends JsonResource
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
            "id"                     => $this->id,
            "name"                   => $this->name,
            "email"                  => $this->email,
            "email_verified_at"      => $this->email_verified_at,
            "created_at"             => $this->created_at,
            "updated_at"             => $this->updated_at,
            "deleted_at"             => $this->deleted_at,
            "added_by_id"            => $this->added_by_id,
            "roles"                  => $this->roles->first(),     
            'serial_id'              => $this->serial_id,
            'stores'                 => in_array('Staff', $this->roles->pluck('title')->toArray()) || in_array('Manager', $this->roles->pluck('title')->toArray()) ? Vendorstaff::where('email', $this->email)->first()->stores : 'not assigned',
        ];
    }
}
