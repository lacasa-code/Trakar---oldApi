<?php

namespace App\Http\Resources\Website\User;

use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteUserRolesApiResource extends JsonResource
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
            'id'         => $this->id,
            'title'      => $this->title,
            'created_at' => $this->created_at,
        ];
    }
}
