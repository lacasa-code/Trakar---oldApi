<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\OrderGetItsDetailsResource;
use App\Http\Resources\User\Shipping\UserSingleShippingApiResource;

class VendorSpecificOrderApiResource extends JsonResource
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
            'id'           => $this->id,
            'user_id'      => $this->user_id,
            'order_number' => $this->order_number,
            'order_total'  => $this->orderDetails()->where('vendor_id', $this->vendor_id)
                              ->sum('total'),
            'expired'      => $this->expired,
            'approved'     => $this->approved,
            'paid'         => $this->paid,
            'status'       => $this->leftt,
            'created_at'   => $this->checkout_time == null ? $this->created_at : $this->checkout_time,
           // 'checkout_time'   => $this->checkout_time,
           // 'sumTotal'     => $this->sumTotal,
            'orderStatus'  => $this->leftt,
            'orderDetails' => OrderGetItsDetailsResource::collection($this->orderDetails()
                                                        ->where('vendor_id', $this->vendor_id)
                                                        ->get()
                                                    ),

            // new added
           // 'shipping'  => $this->shipping,
            'shipping'  => new UserSingleShippingApiResource($this->shipping),
            'payment'  => $this->paymentway,
        ];
    }
}
