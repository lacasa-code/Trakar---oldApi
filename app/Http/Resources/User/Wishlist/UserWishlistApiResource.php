<?php

namespace App\Http\Resources\User\Wishlist;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Http\Resources\User\Product\ProductGetsItsCategoriesResource;
use App\Http\Resources\User\Product\ProductGetsItsTagsResource;
//use App\Http\Controllers\Api\V1\User\Product\ProductGetItsReviewsApiResource;
use App\Http\Resources\User\ProductReviews\ProductReviewsApiResource;
class UserWishlistApiResource extends JsonResource
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
            'id'            => $this->id,
            'user_id'       => $this->user_id,  
            'product_id'    => $this->product_id, 
            'product_type'              => $this->product->producttype_id, 
            'product_price'             => $this->product->producttype_id == 1 ? $this->product->PriceAfterDiscount() : $this->product->holesale_price, 
            'avg_valuations'       => $this->product->productreviews->count() <= 0 ? null : ($this->product->productreviews->avg('evaluation_value') == null ? 0 : round($this->product->productreviews->avg('evaluation_value'), 1)), 
            'actual_price'         => $this->product->producttype_id == 1 ? $this->product->PriceAfterDiscount() : $this->product->holesale_price,
            // 'product_holesale_price'    => $this->product->holesale_price, 
            // 'product_no_orders'         => $this->product->no_of_orders, 
            'user_name'     => $this->user->name,  
            'product_name'  => $this->product->name, 
            'name_en'       => $this->product->name_en,
            'photo'         => $this->product->photo, 
            'time_created'         => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                      ->format('Y-m-d H:i:s'),

            // new added Aug 11 2021
            'name'                 => $this->product->name,
            'description'          => $this->product->description,
            'discount'             => round($this->product->discount, 2),
            'price'                => $this->product->price,
            'actual_price'         => $this->product->producttype_id == 1 ? $this->product->PriceAfterDiscount() : $this->product->holesale_price,

//
            "created_at"         => $this->product->created_at,
            "updated_at"         => $this->product->updated_at,
            "deleted_at"         => $this->product->deleted_at,
// 
            'quantity'             => $this->product->quantity,
            'serial_number'        => $this->product->serial_number,
            'car_made_id'          => $this->product->car_made_id,
            'car_made_name'        => $this->product->car_made,//->car_made,
            // 'car_model_id'      => $this->car_model_id,
            'car_model_name'       => $this->product->car_model,//->carmodel,
            'cartype_id'           => $this->product->cartype_id,
            'cartype_name'         => $this->product->cartype_id == null ? null : $this->product->car_type->type_name,
            // 'year_id'              => $this->year_id,
            "year_from"          => $this->product->year_from_func,
            "year_to"            => $this->product->year_to_func,
           // 'year_name'            => $this->year->year,
            'part_category_id'     => $this->product->part_category_id,
            'part_category_name'   => $this->product->part_category_id == null ? null : $this->product->part_category->category_name,
            'category_id'          => $this->product->category_id,
            'category_name'        => $this->product->category,//->name,
           // 'category_details'     => $this->category,//->name,
            "maincategory_id"   => $this->product->maincategory_id,
            "main_category"      => $this->product->main_category,
            
            'vendor_id'            => $this->product->vendor_id,
            'vendor_name'          => $this->product->vendor->vendor_name,
            'vendor_serial'        => $this->product->vendor->serial,
            'store_id'             => $this->product->store_id,
            'store_name'           => $this->product->store->name,
            'manufacturer_id'      => $this->product->manufacturer_id,
            'prodcountry_id'       => $this->product->prodcountry_id, 
            'manufacturer_name'    => $this->product->manufacturer->manufacturer_name,
            'transmission_id'      => $this->product->transmission_id, 
            'transmission_name'    => $this->product->transmission_id == null ? null : $this->product->transmission->transmission_name,
            'producttype_id'       => $this->product->producttype_id, 
            'producttype_name'     => $this->product->product_type->producttype,
            'origincountry_name'   => $this->product->origin_country->country_name, 
            // "cartype_id"           => $this->cartype_id,
            // "cartype_name"         => $this->car_type->type_name,
            'no_of_orders'         => $this->product->no_of_orders,
            'holesale_price'       => $this->product->holesale_price,
            'count_views'          => $this->product->views->count(), 
            'avg_valuations'       => $this->product->productreviews->avg('evaluation_value') == null ? 0 : round($this->product->productreviews->avg('evaluation_value'), 1), 
            'cart_enable'          => $this->quantity <= 0 && $this->product->producttype_id == 1 ? 0 : 1,
            'wishlist_enable'      => $this->quantity <= 0 && $this->product->producttype_id == 1 ? 0 : 1,
            'serial_id'      => $this->product->serial_id,
            'approved'      => $this->product->approved,
             
            //'product_categories'   => 
                        //ProductGetsItsCategoriesResource::collection($this->categories),
            'product_tags'         => ProductGetsItsTagsResource::collection($this->product->tags),
            'product_reviews'      => ProductReviewsApiResource::collection($this->product->productreviews),
            'count_product_reviews' => $this->product->productreviews->count(),
            //'count_avg_valuations' => $this->evaluations()->count(),
        ];
        
    }
}
