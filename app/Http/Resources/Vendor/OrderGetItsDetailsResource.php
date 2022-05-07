<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Invoice;
use App\Models\Store;
use App\Models\Ticket;
use Auth;

class OrderGetItsDetailsResource extends JsonResource
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
            'name_en'        => $this->product->name_en,
            'photo'          => $this->product->photo,
            'product_serial' => $this->product->serial_number,
            'store_name'     => $this->store->name, 
            'vendor_name'    => $this->vendor->vendor_name,
            'quantity'       => $this->quantity, 
            'price'          => $this->price, 
            'discount'       => $this->discount, 
            'total'          => $this->total, 
            'approved'       => $this->approved,
            'created_at'   => $this->created_at,
            'enable_new_ticket' => Ticket::where('user_id', Auth::user()->id)->where('order_id', $this->order_id)->where('product_id', $this->product_id)->first() != null ? 0 : 1,
            // 'invoiceNum' =>  Invoice::where('order_id', $this->order_id)
                                               //->where('vendor_id', $this->vendor_id)->first()
                                               //->invoice_number,

            'vendor_email'   => $this->vendor->email,
            'company'        => $this->vendor->company_name,
            'phone'          => Store::where('vendor_id', $this->vendor_id)->where('head_center', 1)
                                    ->first()->moderator_phone,
        ];
    }
}
