<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\OrderGetItsDetailsResource;
use App\Http\Resources\User\Shipping\UserSingleShippingApiResource;

class AdminWholesaleOrdersApiResource extends JsonResource
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
            'wholesale_total'  => $this->wholesale_total,
            'need_approval'  => $this->need_approval,
            'expired'      => $this->expired,
            'approved'     => $this->approved,
            'paid'         => $this->paid,
            'status'       => $this->currentStatus,
            'created_at'   => $this->checkout_time == null ? $this->created_at : $this->checkout_time,
           // 'checkout_time'   => $this->checkout_time,
            //'sumTotal'     => $this->sumTotal,
            'count_pieces'    => $this->orderDetails->sum('quantity'),
            'count_products'  => $this->orderDetails->count(),
            'orderStatus'  => $this->orderStatus,
            'currentStatus'  => $this->currentStatus,
            'orderDetails' => OrderGetItsDetailsResource::collection($this->orderDetails),

             // new added
           // 'shipping'  => $this->shipping,
            'shipping'  => new UserSingleShippingApiResource($this->shipping),
            'payment'  => $this->paymentway,
        ];
    }
}
