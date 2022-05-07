<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class SpecificRoleApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            "id"          => $this->id,
            "title"       => $this->title, 
            "created_at"  => $this->created_at, 
            "updated_at"  => $this->updated_at, 
            "deleted_at"  => $this->deleted_at, 
            "added_by_id" => $this->added_by_id, 
            "added_by_name" => $this->added_by_name->name, 
            "permissions" => $this->permissions,
        ];
    }
}
