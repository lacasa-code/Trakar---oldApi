<?php

namespace App\Http\Resources\Website\User\Products;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserFavouriteCarsApiResource extends JsonResource
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
            'user_name'     => $this->user->name,
           
            'car_made_id'   => $this->car_made_id,
            'car_made_name' => $this->car_made_id == null ? null : $this->car_made->car_made,
            'car_made_name_en' => $this->car_made_id == null ? null : $this->car_made->name_en,

            'car_type_id'   => $this->car_type_id,
            'car_type_name' => $this->car_type_id == null ? null : $this->car_type->type_name,
            'car_type_name_en' => $this->car_type_id == null ? null : $this->car_type->name_en,

            'car_model_id'   => $this->car_model_id,
            'car_model_name' => $this->car_model_id == null ? null : $this->car_model->carmodel,
            'car_model_name_en' => $this->car_model_id == null ? null : $this->car_model->name_en,

            'car_year_id'   => $this->car_year_id,
            'car_year_id_name' => $this->car_year_id == null ? null : $this->car_year->year,

            'transmission_id'   => $this->transmission_id,
            'transmission_name' => $this->transmission_id == null ? null : $this->transmission->transmission_name,
            'transmission_name_en' => $this->transmission_id == null ? null : $this->transmission->name_en,

            'time_created'  => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                      ->format('Y-m-d H:i:s'),
        ];
    }
}
