<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketCommentsApiResource extends JsonResource
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
            'id'          => $this->id,
            'ticket_id'   => $this->ticket_id,
            'user_id'     => $this->user_id,
            'user_name'   => $this->user->name,
            'user_role'   => $this->user->roles[0]->title,
            'comment'     => $this->comment,
            'created_at'  => $this->created_at,
        ];
    }
}
