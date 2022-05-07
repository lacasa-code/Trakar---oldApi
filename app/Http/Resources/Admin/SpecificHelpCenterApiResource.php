<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class SpecificHelpCenterApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'question'   => $this->question,
            'answer'     => $this->answer,
            'created_at' => $this->created_at,
            // 'created_by' => $this->created_by,
            // 'status' => $this->status,
        ];
    }
}
