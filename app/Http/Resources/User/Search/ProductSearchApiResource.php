<?php

namespace App\Http\Resources\User\Search;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Http\Resources\User\Product\ProductGetsItsCategoriesResource;
use App\Http\Resources\User\Product\ProductGetsItsTagsResource;
// use App\Http\Controllers\Api\V1\User\Product\ProductGetItsReviewsApiResource;
use App\Http\Resources\User\ProductReviews\ProductReviewsApiResource;
use App\Http\Resources\User\Product\ProductGetsItsPricesResource;
use App\Http\Resources\Api\Admin\Allcategory\EditProductCatsApiResource;
use App\Models\Allcategory;

class ProductSearchApiResource extends JsonResource
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
            'id'                   => $this->id,
            'name'                 => $this->name,
            'description'          => $this->description,
            'name_en'            => $this->name_en,
           'description_en'     => $this->description_en,
            'discount'             => round($this->discount, 2),
            'price'                => $this->price,
            'actual_price'         => $this->producttype_id == 1 ? $this->PriceAfterDiscount() : $this->holesale_price,
            'quantity'             => $this->quantity,
           
            "width"        => $this->width,
            "height"       => $this->height,
            "size"         => $this->size,

            'tyres_belong' => Allcategory::where('id', $this->allcategory_id)->first()->need_attributes == 1 ? 1 : 0,

            "allcategory"  => EditProductCatsApiResource::collection($this->allcategory),
            'serial_number'        => $this->serial_number,
            'car_made_id'          => $this->car_made_id,
            'car_made_name'        => $this->car_made,//->car_made,
            //'car_model_id'         => $this->car_model_id,
            'car_model_name'       => $this->car_model,// ->carmodel,
            'cartype_id'           => $this->cartype_id,
            'cartype_name'         => $this->cartype_id == null ? null : $this->car_type->type_name,
           // 'year_id'              => $this->year_id,
            "year_from"          => $this->year_from_func,
            "year_to"            => $this->year_to_func,
           // 'year_name'            => $this->year->year,
            'part_category_id'     => $this->part_category_id,
            'part_category_name'   => $this->part_category_id == null ? null : $this->part_category->category_name, 
            'category_id'          => $this->category_id,
            'category_name'        => $this->category,//->name,
           // 'category_details'     => $this->category,//->name,
            'vendor_id'            => $this->vendor_id,
            'vendor_name'          => $this->vendor->vendor_name,
            'vendor_serial'          => $this->vendor->serial,
            'store_id'             => $this->store_id,
            'store_name'           => $this->store->name,
            'manufacturer_id'      => $this->manufacturer_id,
            'prodcountry_id'       => $this->prodcountry_id, 
            'manufacturer_name'    => $this->manufacturer->manufacturer_name,
            'transmission_id'      => $this->transmission_id, 
            'transmission_name'    => $this->transmission_id == null ? null : $this->transmission->transmission_name,
            'producttype_id'       => $this->producttype_id,
            'producttype_name'     => $this->product_type->producttype,
            'origincountry_name'   => $this->origin_country->country_name, 
            // "cartype_id"           => $this->cartype_id,
            // "cartype_name"         => $this->car_type->type_name,
            'no_of_orders'         => $this->no_of_orders,
            'holesale_price'       => $this->holesale_price,
            'count_views'          => $this->views->count(), 
            'avg_valuations'       => $this->productreviews->avg('evaluation_value') == null ? 0 : round($this->productreviews->avg('evaluation_value'), 1), 
            'cart_enable'          => $this->quantity <= 0 && $this->producttype_id == 1 ? 0 : 1,
            'wishlist_enable'      =>  1, 
            //$this->quantity <= 0 && $this->producttype_id == 1 ? 0 : 1,
            'media'                => $this->media,
            'photo'                => $this->photo,
            'serial_id'      => $this->serial_id,
            'approved'      => $this->approved,
             
            //'product_categories'   => 
                        //ProductGetsItsCategoriesResource::collection($this->categories),
            'product_tags'          => ProductGetsItsTagsResource::collection($this->tags),
            // 'product_prices'        => ProductGetsItsPricesResource::collection($this->product_prices),
            'product_reviews'       => ProductReviewsApiResource::collection($this->productreviews),
            'count_product_reviews' => $this->productreviews->count(),
            'count_avg_valuations'  => $this->evaluations()->count(),

           //'created_at'           => $this->created_at,
           'time_created'         => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                      ->format('Y-m-d H:i:s'),
             'in_cart'             => $this->in_cart,
             'in_wishlist'         => $this->in_wishlist,
             'in_favourites'       => $this->in_favourites,
        ];
    }
}