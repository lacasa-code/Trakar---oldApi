<?php

namespace App\Http\Resources\Website\User\FavouriteProducts;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserSingleFavouriteProductApiResource extends JsonResource
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
            'id'            => $this->id, 
            'user_id'       => $this->user_id,  
            'product_id'    => $this->product_id, 
            'user_name'     => $this->user->name,  
            'product_name'  => $this->product->name, 
            'time_created'         => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                      ->format('Y-m-d H:i:s'),
        ];
        
    }
}
