<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginDataResource extends JsonResource
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
             "id"      => $this->first()->id,
             "name"    => $this->first()->name,
             "email"   => $this->first()->email,
             "roles"   => $this->first()->roles,
               // 'permissions' => $this->first()->roles->permissions,
        ];
    }
}
