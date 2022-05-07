<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Vendor\OrderGetItsDetailsResource;

class VendorOrdersApiExceptResource extends JsonResource
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
            'sumTotal'     => $this->sumTotal,
            'orderStatus'  => $this->orderStatus,
            //'foog'         => $this->apple,
            'orderDetails' => OrderGetItsDetailsResource::collection($this->orderDetails),
        ];
    }
}
