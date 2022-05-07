<?php

namespace App\Http\Resources\Website\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Website\User\WebsiteUserRolesApiResource;

class WebsiteLoginVendorApiResource extends JsonResource
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
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'last_name' => $this->last_name,
            'phone_no'     => $this->phone_no == null ? null : $this->phone_no,
            'birthdate'     => $this->birthdate,
            'gender'     => $this->gender,
            'created_at'        => $this->created_at,
            'token'             => $this->token,
            'roles'             => $this->roles,
            'vendor_details'    => $this->vendor_details,
        ];
    }
}
