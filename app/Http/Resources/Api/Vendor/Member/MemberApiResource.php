<?php

namespace App\Http\Resources\Api\Vendor\Member;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class MemberApiResource extends JsonResource
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

           // "id"         => $this->id,
            "name"   => User::where('email', $this->email)->first() == null ? 'not registered yet' : User::where('email', $this->email)->first()->name,
            "email"      => $this->email,
            "role_name"  => $this->role_name,

        ];
    }
}
