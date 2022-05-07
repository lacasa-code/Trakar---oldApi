<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\OrderGetItsDetailsResource;
use App\Http\Resources\User\Shipping\UserSingleShippingApiResource;
use App\Models\Fixedshipping;
 
class AdminApiSpecificOrderResource extends JsonResource
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
            'order_total'  => $this->order_total,
            'expired'      => $this->expired,
            'approved'     => $this->approved,
            'paid'         => $this->paid,
            'status'       => $this->status,
            'created_at'   => $this->checkout_time == null ? $this->created_at : $this->checkout_time,
           // 'checkout_time'   => $this->checkout_time,
            'paymentway'   => $this->payment_id == null ? null : $this->paymentway,
            'shipping'     => $this->shipping_address_id == null ? null : $this->shipping,
            //'sumTotal'     => $this->sumTotal,
            'orderStatus'  => $this->orderStatus,
            'orderDetails' => OrderGetItsDetailsResource::collection($this->orderDetails),

            // new added
          //  'shipping'  => $this->shipping,
            'shipping'        => new UserSingleShippingApiResource($this->shipping),
            'fixed_shipping'  => new UserSingleShippingApiResource(Fixedshipping::where('order_id', $this->id)->first()),
            'payment'  => $this->paymentway,
        ];
    }
}
