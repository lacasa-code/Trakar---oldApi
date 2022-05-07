<?php

namespace App\Http\Resources\Api\V1\Vendor;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Website\Products\SpecificFrontProductsQuestionsApiResource;

class ProdQuestionsApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return[
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'product_id'       => $this->product_id,
            'vendor_id'        => $this->vendor_id,
            'user_name'        => $this->user->name,
            'product'          => new SpecificFrontProductsQuestionsApiResource($this->product),
            'vendor'           => $this->AddVendor,
            'body_question'    => $this->body_question,
            'answer'           => $this->answer,
            'reply'            => $this->answer == null ? 'no reply yet': 'replied',
            'lang'             => $this->lang,
            'status'           => $this->status, 
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at, 

        ];
    }
}
