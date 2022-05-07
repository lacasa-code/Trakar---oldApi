<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminApiSpecificInvoiceResource extends JsonResource
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
            // 'vendorDetails'    => $this->vendorDetails,
            'invoice_number'   => $this->invoice_number,
            'invoice_total'    => $this->invoice_total,
            'status'           => $this->status,
        ];
    }
}
