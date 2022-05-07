<?php

namespace App\Http\Resources\User\Cart;

use Illuminate\Http\Resources\Json\JsonResource;

class CartOrderDetailsToAdminApiResource extends JsonResource
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
            'id'             => $this->id,
            'order_id'       => $this->order_id, 
            'product_id'     => $this->product_id, 
            'store_id'       => $this->store_id, 
            'vendor_id'      => $this->vendor_id,
            'product_name'   => $this->product->name,
            'product_image'  => $this->product->photo,
            'product_serial' => $this->product->serial_number, 
            'store_name'     => $this->store->name, 
            'vendor_name'    => $this->vendor->vendor_name,
            'quantity'       => $this->quantity, 
            'price'          => $this->price, 
            'discount'       => $this->discount, 
            'total'          => $this->total, 
            'approved'       => $this->approved,
            'created_at'     => $this->created_at,
            // 'invoiceNum' =>  Invoice::where('order_id', $this->order_id)
                                              // ->where('vendor_id', $this->vendor_id)->first()
                                              // ->invoice_number,
        ];
    }
}
