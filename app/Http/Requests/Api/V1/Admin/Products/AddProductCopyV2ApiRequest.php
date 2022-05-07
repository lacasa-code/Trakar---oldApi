<?php

namespace App\Http\Requests\Api\V1\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use App\Models\Product;

class AddProductCopyV2ApiRequest extends FormRequest
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

           /* 'maincategory_id' => [
                'required',
                'integer',
                'exists:maincategories,id,deleted_at,NULL' // adited validation ahmed
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:product_categories,id,deleted_at,NULL' // adited validation ahmed
            ],

            'part_category_id' => [
                'nullable',
                'integer',
                'exists:part_categories,id,deleted_at,NULL' // adited validation ahmed
            ], */

           /* 'qty_reminder' => [
                'nullable',
                'integer',
                'min:1' // adited validation ahmed
            ],/*

            'cartype_id' => [
                //'required',
                'nullable',
                'integer',
                'exists:cartypes,id,deleted_at,NULL' // adited validation ahmed
            ],

            'models'       => [
                'nullable',
            ],
            
            'tags'       => [
                'required',
            ],
            'car_made_id'      => [
                'nullable',
                'integer',
               // 'exists:car_mades,id',
                'exists:car_mades,id,deleted_at,NULL' // adited validation ahmed
            ],

            'year_from'          => [
                'nullable',
                'integer',
                'exists:car_years,id,deleted_at,NULL' // adited validation ahmed
            ],

            'year_to'          => [
                'nullable',
                'integer',
               'exists:car_years,id,deleted_at,NULL' // adited validation ahmed
            ],

            'manufacturer_id' => [
                'required',
                'integer',
                'exists:manufacturers,id,deleted_at,NULL' // adited validation ahmed
            ],

            'transmission_id' => [
                'nullable',
                'integer',
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
           /* 'name'             => [
                'string',
                'required',
                // 'unique:products,name',
            ],
            'description'             => [
                'string',
                'required',
                'min:5',
            ],*/

            'allcategory' => [
                'required',
              //  'array',
               
            ],

          /*  'allcategory_id.*' => [
                'required',
                'integer',
                'exists:allcategories,id,deleted_at,NULL' // adited validation ahmed
            ], */
            
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
            
            /* 'quantity' => [
                'required',
                'integer',
                'min:1',
            ], */
            'photo.*' => [
                //'required',
             //   'file',
              //  'image',
                'mimes:png,gif,jpeg,jpg',
                'max:1048',
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
        $messages =  [
                'name.string'    => __('ahmed.product_name_string'),
                'name.required'  => __('ahmed.product_name_required'),
                //'name.unique'    => __('ahmed.product_name_unique'),
               // 'photo.*.file'     => __('ahmed.prod_image_file'),
              //  'photo.*.image'    => __('ahmed.prod_image_file'),
                'photo.*.mimes'    => __('ahmed.prod_image_mimes'),
                'photo.*.max'      => __('ahmed.prod_image_max'),
        ];

            /*foreach ($this->file('photo') as $key => $val) {
            $messages["photo.($key+1)"] = "$val is not a valid image";
            }*/
        return $messages;
    }
}
