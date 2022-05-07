<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Store;
use Gate;
use Illuminate\Http\Response;

class AddStoreApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('stores_create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                //'unique:stores,name',
            ],

            'address' => [
                'required',
                'string',
            ],

            'lat' => [
                'required',
                'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/',
            ],

             'long' => [
                'required',
                'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/',
            ],

            //'vendor_id' => [
            //    'required',
            //    'integer',
            //    'exists:add_vendors,id',
            // 'exists:add_vendors,id,deleted_at,NULL' // adited validation ahmed
            //],

            'country_id' => [
                'required',
                'integer',
                // 'exists:add_vendors,id',
                'exists:countries,id,deleted_at,NULL'  // adited validation ahmed
            ],

            'area_id' => [
                'required',
                'integer',
                //'exists:add_vendors,id',
                'exists:areas,id,deleted_at,NULL'  // adited validation ahmed
            ],

            'city_id' => [
                'required',
                'integer',
                //'exists:add_vendors,id',
                'exists:cities,id,deleted_at,NULL'  // adited validation ahmed
            ],

            /*'moderator_name' => [
                'required',
                'string',
            ],*/

            'moderator_phone' => [
                'required',
               // 'regex:/^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/',
            ],

             'moderator_alt_phone' => [
                'nullable',
              //  'regex:/^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('ahmed.store_name_required'),
            'name.string'   => __('ahmed.store_name_string'),
            'name.unique'   => __('ahmed.store_name_unique'),
        ];
    }
}
