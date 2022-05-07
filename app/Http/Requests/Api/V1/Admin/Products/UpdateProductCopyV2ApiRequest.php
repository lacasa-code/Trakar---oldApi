<?php

namespace App\Http\Requests\Api\V1\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;

class UpdateProductCopyV2ApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //return true;
         return Gate::allows('product_edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            /*'maincategory_id' => [
                'required',
                'integer',
                'exists:maincategories,id,deleted_at,NULL' // adited validation ahmed
            ],
          
          'category_id' => [
                'required',
                'integer',
                'exists:product_categories,id,deleted_at,NULL' // adited validation ahmed
            ],*/

            'allcategory' => [
                'required',
              //  'array',
               
            ],

            /*'allcategory_id.*' => [
                'required',
                'integer',
                'exists:allcategories,id,deleted_at,NULL' // adited validation ahmed
            ], */

           /* 'cartype_id' => [
                'nullable',
                'integer',
                'exists:cartypes,id,deleted_at,NULL' // adited validation ahmed
            ], */

            /*'categories'       => [
                'required',
            ],*/

            'tags'       => [
                'required',
            ],

            'car_made_id'      => [
                'nullable',
                'integer',
                'exists:car_mades,id,deleted_at,NULL' // adited validation ahmed
            ],

            'models'       => [
               // 'required',
                'nullable',
            ],

            'qty_reminder' => [
                'required',
                'integer',
                'min:1' // adited validation ahmed
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

           /* 'car_model_id'     => [
                'required',
                'integer',
                'exists:car_models,id,deleted_at,NULL' // adited validation ahmed
            ],*/
           /* 'year_id'          => [
                'required',
                'integer',
                'exists:car_years,id,deleted_at,NULL' // adited validation ahmed
            ],*/
         /*   'part_category_id' => [
                'nullable',
                'integer',
                //'exists:part_categories,id',
                'exists:part_categories,id,deleted_at,NULL' // adited validation ahmed
            ], */
            //
            'manufacturer_id' => [
                'required',
                'integer',
              //  'exists:manufacturers,id',
                'exists:manufacturers,id,deleted_at,NULL' // adited validation ahmed
            ],
            'transmission_id' => [
                'nullable',
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
              //  'exists:prodcountries,id',
                'exists:prodcountries,id,deleted_at,NULL' // adited validation ahmed
            ],
           
           /* 'description'             => [
                'string',
                'required',
                'min:5',
            ],
            //
            'name'             => [
                'string',
                'required',
                // 'unique:products,name,'.request()->route('product')->id,
            ],*/

           /* 'discount'            => [ // null value or valid numeric decimal number (5 > 80)
                'nullable',
                'numeric',
                'min:5',
                'max:80',
            ],*/
            /*'price'            => [
                'required',
                'numeric',
                'min:1', 
            ],*/
            'serial_number'            => [
                'required',
               // 'numeric',
               // 'unique:products,serial_number,'.request()->route('product')->id,
            ],
            'store_id' => [
                'required',
                'integer',
               // 'exists:stores,id',
                'exists:stores,id,deleted_at,NULL' // adited validation ahmed
            ],
             'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'photo.*' => [
                'nullable',
                'file',
                'image',
                'mimes:png,gif,jpeg,jpg',
                'max:1048',
            ],
            'photo' => [
                //'required',
                'nullable',
                'array',
               // 'image',
            ],
        ];
    }
}
