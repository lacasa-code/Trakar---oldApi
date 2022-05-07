<?php

namespace App\Http\Resources\Website\User\FavouriteProducts;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\Allcategory;
use App\Http\Resources\Api\Admin\Allcategory\EditProductCatsApiResource;

class UserFavouriteProductsApiResource extends JsonResource
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
            'name'                 => $this->product->name,
            'description'          => $this->product->description,
            'discount'             => $this->product->discount,
            'price'                => $this->product->price,
            'quantity'             => $this->product->quantity,

            "width"        => $this->product->width,
            "height"       => $this->product->height,
            "size"         => $this->product->size,

            'tyres_belong' => Allcategory::where('id', $this->product->allcategory_id)->first()->need_attributes == 1 ? 1 : 0,
            "allcategory"  => EditProductCatsApiResource::collection($this->product->allcategory),

            'serial_number'        => $this->product->serial_number,
            'car_made_id'          => $this->product->car_made_id,
            'car_made_name'        => $this->product->car_made->car_made,
            'car_model_id'         => $this->product->car_model_id,
            'car_model_name'       => $this->product->car_model->carmodel,
            'cartype_id'           => $this->product->cartype_id,
            'cartype_name'         => $this->product->car_type->type_name,
            'year_id'              => $this->product->year_id,
            'year_name'            => $this->product->year->year,
            'part_category_id'     => $this->product->part_category_id,
            'part_category_name'   => $this->product->part_category->category_name,
            'category_id'          => $this->product->category_id,
            'category_name'        => $this->product->part_category->category->name,
            'vendor_id'            => $this->product->vendor_id,
            'vendor_name'          => $this->product->vendor->vendor_name,
            'store_id'             => $this->product->store_id,
            'store_name'           => $this->product->store->name,
            'manufacturer_id'      => $this->product->manufacturer_id,
            'prodcountry_id'       => $this->product->prodcountry_id, 
            'manufacturer_name'    => $this->product->manufacturer->manufacturer_name,
            'transmission_id'      => $this->product->transmission_id, 
            'transmission_name'    => $this->product->transmission->transmission_name,
            'origincountry_name'   => $this->product->origin_country->country_name, 
            "cartype_id"           => $this->product->cartype_id,
            "cartype_name"         => $this->product->car_type->type_name,
            'count_views'          => $this->product->views->count(), 
            'avg_valuations'       => $this->product->evaluations()->avg('evaluation_value'),
            'cart_enable'          => $this->product->quantity <= 0 ? 0 : 1,
            'wishlist_enable'      => $this->product->quantity <= 0 ? 0 : 1,
            'media'                => $this->product->media,
            'photo'                => $this->product->photo,
            'count_avg_valuations' => $this->product->evaluations()->count(),
            'user_name'     => $this->user->name,  
            'product_name'  => $this->product->name, 
            'time_created'         => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                      ->format('Y-m-d H:i:s'),
        ];
        
    }
}
