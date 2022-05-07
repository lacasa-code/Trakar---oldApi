<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    	/*
    	return [
    		'id'         => $this->id,
    		'title'      => $this->title,
    		'created_at' => $this->created_at,
    		'updated_at' => $this->updated_at,
    	]; */
    }
}
