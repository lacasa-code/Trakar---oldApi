<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\Shipping\UserSingleShippingApiResource;
use App\Models\Store;

class VendorApiSpecificInvoiceResource extends JsonResource
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
            'id'               => $this->id,
            'order_id'         => $this->order_id,
            'order_number'     => $this->order->order_number,
            'vendor_id'        => $this->vendor_id,
            'vendor_name'      => $this->vendor->vendor_name,
            'vendor_email'     => $this->vendor->email,
            'user_name'        => $this->order->user->name,
           // 'user_address'     => $this->order->shipping,
            'user_address'  => new UserSingleShippingApiResource($this->order->shipping),

            // new added
            'payment'  => $this->order->paymentway,

            // 'vendorDetails'    => $this->vendorDetails,
            'invoice_number'   => $this->invoice_number,
            'invoice_total'    => $this->invoice_total,
            'status'           => $this->status,
            'created_at'       => $this->created_at,

             //'vendor_email'   => $this->vendor->email,
            'company'        => $this->vendor->company_name,
            'phone'          => Store::where('vendor_id', $this->vendor_id)->where('head_center', 1)
                                    ->first()->moderator_phone,
        ];
    }
}
