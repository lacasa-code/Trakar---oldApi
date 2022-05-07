<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Vendor\OrderGetItsDetailsResource;
use App\Http\Resources\Vendor\TicketCommentsApiResource;
use App\Http\Resources\User\Shipping\UserSingleShippingApiResource;
use App\Models\Ticket;
use Auth;

class TicketsApiResource extends JsonResource
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
            'ticket_no'        => $this->ticket_no,
            'title'            => $this->title,
            'ticketpriority_id'  => $this->ticketpriority_id,
            'priority'     => $this->ticketPriority->name,
            'message'          => $this->message,
            'status'           => $this->status,
            'category_id'      => $this->category_id,
            'category_name'    => $this->ticketCategory->name,
            'user_id'          => $this->user_id,
            'user_name'        => $this->ticketUser->name,
            'user_email'       => $this->ticketUser->email,
            'user_phone'       => $this->ticketUser->phone_no,
            'vendor_id'        => $this->vendor_id,
            'vendor_name'      => $this->ticketVendor->vendor_name,
            'product_id'        => $this->product_id,
            'product_name'      => $this->product->name,
            'name_en'           => $this->product->name_en,
            'enable_new_ticket' => Ticket::where('user_id', Auth::user()->id)->where('order_id', $this->order_id)->where('product_id', $this->product_id)->first() != null ? 0 : 1,
            'order_id'         => $this->order_id,
            'attachment'       => $this->attachment,
            'answer'          => $this->answer,
            'comments'          => TicketCommentsApiResource::collection($this->ticketComments),
            'case'            => $this->case,
            // 'reply'           => $this->answer == null ? 'no reply yet': 'replied',
            'reply'           => $this->ticketComments->count() > 0 ? 'replied' : 'no reply yet',
            
           // 'order'            => $this->ticketOrder,
            'orderDetails' => OrderGetItsDetailsResource::collection($this->ticketOrder->orderDetails->where('vendor_id', $this->vendor_id)->where('approved', 1)),    
            'order_number'     => $this->order_id == null ? null : $this->ticketOrder->order_number,
            'order_created_at' => $this->order_id == null ? null : $this->ticketOrder->created_at,
            'vendor_id'        => $this->vendor_id,
            'vendor_name'      => $this->vendor_id == null ? null : $this->ticketVendor->vendor_name,
            'vendor_email'     => $this->vendor_id == null ? null : $this->ticketVendor->email,
            'created_at'       => $this->created_at,

            // new added
            'shipping'  => new UserSingleShippingApiResource($this->ticketOrder->shipping),
            'payment'  => $this->ticketOrder->paymentway,
        ];
    }
}
