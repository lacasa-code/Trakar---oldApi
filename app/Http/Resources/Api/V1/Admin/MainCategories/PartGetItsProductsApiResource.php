<?php

namespace App\Http\Resources\Api\V1\Admin\MainCategories;

use Illuminate\Http\Resources\Json\JsonResource;

class PartGetItsProductsApiResource extends JsonResource
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
        return [
            "id"                 => $this->id,
            "name"               => $this->name,
            "description"        => $this->description,
            "name_en"               => $this->name_en,
            "description_en"        => $this->description_en,
            "price"              => $this->price,
            "discount"           => $this->discount,
            "created_at"         => $this->created_at,
            "updated_at"         => $this->updated_at,
            "deleted_at"         => $this->deleted_at,
            "car_made_id"        => $this->car_made_id,
            "car_model_id"       => $this->car_model_id,
            "cartype_id"         => $this->cartype_id,
            "year_id"            => $this->year_id,
            //
            "year_from"          => $this->year_from_func(),
            "year_to"            => $this->year_to_func(),
            //
            "part_category_id"   => $this->part_category_id,
            "category_id"        => $this->category_id,
            "vendor_id"          => $this->vendor_id,
            "store_id"           => $this->store_id,
            "quantity"           => $this->quantity,
            "serial_number"      => $this->serial_number,
            "photo"              => $this->photo,
            "tags"               => $this->tags,
            "category"           => $this->category,
            "car_made"           => $this->car_made,
            "car_model"          => $this->car_model,
            'car_type'          => $this->car_type,
            "year"               => $this->year,
            "part_category"      => $this->part_category,
            "store"              => $this->store,
            "vendor"             => $this->vendor,
            "manufacturer"       => $this->manufacturer,
            "origin_country"     => $this->origin_country,
            "transmission_id"    => $this->transmission_id,
            "transmission"       => $this->transmission,
            'producttype_id'     => $this->product_type,
            'serial_coding'      => $this->serial_coding,
            'serial_id'          => $this->serial_id,
            'holesale_price'     => $this->holesale_price,
            'no_of_orders'       => $this->no_of_orders,
            "media"              => $this->media,
        ];
    }
}