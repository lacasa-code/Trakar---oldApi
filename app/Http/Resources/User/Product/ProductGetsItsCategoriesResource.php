<?php

namespace App\Http\Resources\User\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ProductGetsItsCategoriesResource extends JsonResource
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
            'name'         => $this->name,
            'description'  => $this->description,
            'time_created' => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                        ->format('Y-m-d H:i:s'),
        ];
    }
}
