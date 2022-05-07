<?php

namespace App\Http\Resources\User\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductGetsItsPricesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return[
            'id'                 => $this->id,
            'product_id'         => $this->product_id,
            'producttype_id'     => $this->producttype_id,
            'product_name'       => $this->product->name,
            'producttype_name'   => $this->product_type->producttype,
            'num_of_orders'      => $this->num_of_orders,
            'price'              => $this->price,
            'serial_coding_seq'  => $this->serial_coding_seq,
            'currency'           => $this->currency,
            'created_at'         => $this->created_at,
           /* 'time_created'       => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                        ->format('Y-m-d H:i:s'),*/
        ];
    }
}
