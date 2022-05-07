<?php

namespace App\Http\Resources\Vendor\Reports;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Vendor\OrderGetItsDetailsResource;

class AdvancedApiReportGeneralCaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return[
            'id'           => $this->id,
            'user_id'      => $this->user_id,
            'order_number' => $this->order_number,
            'order_total'  => $this->order_total,
            'expired'      => $this->expired,
            'approved'     => $this->approved,
            'paid'         => $this->paid,
            'status'       => $this->status,
            'created_at'   => $this->created_at,
            // 'sumTotal'     => $this->sumTotal,
            'orderStatus'  => $this->orderStatus,
            'orderDetails' => OrderGetItsDetailsResource::collection($this->orderDetails),
        ];
    }
}
