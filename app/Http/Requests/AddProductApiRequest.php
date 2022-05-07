<?php

namespace App\Http\Requests;

use App\Models\Product;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class AddProductApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
          return Gate::allows('product_create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
     public function rules()
    {
        return [

            'category_id' => [
                'required',
                'integer',
                'exists:product_categories,id,deleted_at,NULL' // adited validation ahmed
            ],

            'cartype_id' => [
                'required',
                'integer',
                'exists:cartypes,id,deleted_at,NULL' // adited validation ahmed
            ],

          /*  'categories'       => [
                'required',
            //    'array',
            ],*/
            
            'tags'       => [
                'required',
            //    'array',
            ],
            'car_made_id'      => [
                'required',
                'integer',
               // 'exists:car_mades,id',
                'exists:car_mades,id,deleted_at,NULL' // adited validation ahmed
            ],
            'car_model_id'     => [
                'required',
                'integer',
               // 'exists:car_models,id',
                'exists:car_models,id,deleted_at,NULL' // adited validation ahmed
            ],
            'year_id'          => [
                'required',
                'integer',
              //  'exists:car_years,id',
                'exists:car_years,id,deleted_at,NULL' // adited validation ahmed
            ],
            'part_category_id' => [
                'required',
                'integer',
               // 'exists:part_categories,id',
                'exists:part_categories,id,deleted_at,NULL' // adited validation ahmed
            ],
            //
            'manufacturer_id' => [
                'required',
                'integer',
               // 'exists:manufacturers,id',
                'exists:manufacturers,id,deleted_at,NULL' // adited validation ahmed
            ],
            'transmission_id' => [
                'required',
                'integer',
               // 'exists:transmissions,id',
                'exists:transmissions,id,deleted_at,NULL' // adited validation ahmed
            ],
            'producttype_id' => [
                'required',
                'integer',
                'exists:producttypes,id,deleted_at,NULL' // adited validation ahmed
            ],
            'prodcountry_id' => [
                'required',
                'integer',
               // 'exists:prodcountries,id',
                'exists:prodcountries,id,deleted_at,NULL' // adited validation ahmed
            ],
            //
            'name'             => [
                'string',
                'required',
                // 'unique:products,name',
            ],
            'description'             => [
                'string',
                'required',
                'min:5',
            ],
            /*'price'            => [
                'required',
                'numeric',
                'min:1',
            ],*/
           /* 'discount'            => [
                'nullable',
                'numeric',
                'min:5',
                'max:80',
            ],*/
            'serial_number'            => [
                'required',
               // 'numeric',
               // 'unique:products,serial_number',
            ],
            'store_id' => [
                'required',
                'integer',
                //'exists:stores,id',
                'exists:stores,id,deleted_at,NULL' // adited validation ahmed
            ],
             'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'photo.*' => [
                //'required',
                'file',
                'image',
                'mimes:png,gif,jpeg,jpg',
                'max:500',
            ],
            'photo' => [
                // 'required',
                'array',
               // 'image',
            ],

        ];
    }

    public function messages()
    {
        return [
                'name.string'    => __('ahmed.product_name_string'),
                'name.required'  => __('ahmed.product_name_required'),
                'name.unique'    => __('ahmed.product_name_unique'),
        ];
    }
}
