<?php

namespace App\Http\Resources\User\ProductReviews;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Http\Resources\Website\User\EvaluationProducts\UserEvaluationProductsApiResource;

class ProductReviewsApiResource extends JsonResource
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
            'id'              => $this->id,
            'body_review'     => $this->body_review,
            'user_id'         => $this->user_id,
            'product_id'      => $this->product_id,
            'user_name'       => $this->user->name,
            'product_name'    => $this->product->name,
            'evaluation_value' => round($this->evaluation_value, 1),
           // 'evaluations'     => UserEvaluationProductsApiResource::collection($this->product->evaluations->where('user_id', $this->user_id)),
            'created_at'      => $this->created_at,
            'time_created'    => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                        ->format('Y-m-d H:i:s'),
           // 'status'              => $this->status,
        ];
    }
}
