<?php

namespace App\Http\Resources\User\Cart;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\Cart\CartOrderDetailsApiResource;
use App\Http\Resources\User\Shipping\UserSingleShippingApiResource;

class CartContentsApiResource extends JsonResource
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
            'user_name'    => $this->user->name,
            'order_number' => $this->order_number,
            'order_total'  => $this->order_total,
            'expired'      => $this->expired,
            'approved'     => $this->approved,
            'paid'         => $this->paid,
            'status'       => $this->currentStatus,
            'created_at'   => $this->checkout_time,
            'paymentway'   => $this->payment_id == null ? null : $this->paymentway,
            'shipping'     => $this->shipping_address_id == null ? null : new UserSingleShippingApiResource($this->shipping),
            //'sumTotal'     => $this->sumTotal,
            'orderStatus'  => $this->currentStatus,
            'count_pieces'    => $this->orderDetails->sum('quantity'),
            'count_products'  => $this->orderDetails->count(),
            'orderDetails' => CartOrderDetailsApiResource::collection($this->orderDetails),
           // 'arr' => $this->orderDetails->pluck('approved')->toArray(),
        ];
    }
}
