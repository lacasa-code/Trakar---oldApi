<?php

namespace App\Http\Resources\User\Shipping;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserSingleShippingApiResource extends JsonResource
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
            'id'                   => $this->id,
            'user_id'              => $this->user_id,
            'user_name'            => $this->user->name,
            'status'               => $this->status,
            'recipient_name'       => $this->recipient_name,
            'recipient_phone'      => $this->recipient_phone,
            'recipient_alt_phone'  => $this->recipient_alt_phone,
            'recipient_email'      => $this->recipient_email,
            'address'              => $this->address,
            'state'                => $this->country,
            'area'                 => $this->is_area,
            'city'                 => $this->is_city,
            'default'              => $this->default,
            // 'country_code'         => $this->country_code,
            // 'postal_code'          => $this->postal_code,
            // 'latitude'             => $this->latitude,
            // 'longitude'            => $this->longitude,
            // 30 may 2021
            'last_name'            => $this->last_name,
            'street'               => $this->street,
            'district'             => $this->district,    
            'home_no'              => $this->home_no,
            'floor_no'             => $this->floor_no, 
            'apartment_no'         => $this->apartment_no, 
            'telephone_no'         => $this->telephone_no,
            'nearest_milestone'    => $this->nearest_milestone,
            'notices'              => $this->notices,
            'created_at'           => $this->created_at,
            'time_created'         => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                      ->format('Y-m-d H:i:s'),
        ];
    }
}
