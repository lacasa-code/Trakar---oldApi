<?php

namespace App\Http\Resources\User\Cart;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutContentsToAdminApiResource extends JsonResource
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
            'order_wholesale_total' => $this->order_wholesale_total,
             //$this->order_total,
            'expired'      => $this->expired,
            'approved'     => $this->approved,
            'paid'         => $this->paid,
            'shipping'     => $this->shipping,
            'payment'      => $this->paymentway,
            'status'       => $this->status,
            'created_at'   => $this->created_at,
            'count_pieces'    => $this->orderDetails->sum('quantity'),
            'count_products'  => $this->orderDetails->count(),
            //'sumTotal'     => $this->sumTotal,
            'orderStatus'  => $this->orderStatus,
            'orderDetails' => CartOrderDetailsToAdminApiResource::collection($this->orderDetails->where('producttype_id', 2)),
        ];
    }
}
