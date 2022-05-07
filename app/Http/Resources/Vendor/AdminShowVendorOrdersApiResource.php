<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Vendor\OrderGetItsDetailsResource;
use App\Http\Resources\User\Shipping\UserSingleShippingApiResource;

class AdminShowVendorOrdersApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
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
            'status'       => $this->leftt,
            'created_at'   => $this->checkout_time == null ? $this->created_at : $this->checkout_time,
            'leftApproval'   => $this->leftApproval,
            // 'sumTotal'     => $this->sumTotal,
            'orderStatus'  => $this->leftt,
            //'currentStatus'  => $this->currentStatus,
            'count_pieces'    => $this->orderDetails->sum('quantity'),
            'count_products'  => $this->orderDetails->count(),
            'orderDetails' => OrderGetItsDetailsResource::collection($this->orderDetails),
            'need_approval'  => $this->need_approval,
           // 'leftt'          => $this->leftt,

            // new added
           // 'shipping'  => $this->shipping,
            'shipping'  => new UserSingleShippingApiResource($this->shipping),
            'payment'  => $this->paymentway,
        ];
    }
}
