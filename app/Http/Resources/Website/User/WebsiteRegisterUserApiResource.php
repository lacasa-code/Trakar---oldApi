<?php

namespace App\Http\Resources\Website\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Website\User\WebsiteUserRolesApiResource;

class WebsiteRegisterUserApiResource extends JsonResource
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
            'token'             => $this->token,
            'serial_id'         => $this->serial_id,
            'created_at'        => $this->created_at,
            'roles'             => WebsiteUserRolesApiResource::collection($this->roles),
        ];
    }
}
