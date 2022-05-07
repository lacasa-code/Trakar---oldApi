<?php

namespace App\Http\Requests\Website\User\Shipping;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateShippingApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

               'recipient_name'        => [
                'required',
                'string',
            ],

            'recipient_phone'       => [
                'required',
                'string',
                'min:10',
                'max:17',
                //'starts_with:+'
            ],

          /*  'recipient_alt_phone'   => [
                'nullable',
                'regex:/^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/',
            ],*/

           /* 'recipient_email'       => [
                'nullable',
                'email',
            ],*/

            'address'               => [
                'nullable',
                'string',
            ],

             'country_id'                 => [
                'required',
                'integer',
                'exists:countries,id,deleted_at,NULL' // adited validation ahmed

            ],

            'area_id'  => [
                'required',
                'integer',
                'exists:areas,id,deleted_at,NULL' // adited validation ahmed
            ],

            'city_id'                  => [
                'required',
                'integer',
                'exists:cities,id,deleted_at,NULL' // adited validation ahmed
            ],
            'street'                  => [
                'required',
                'string',
            ],


            /*'country_code'          => [
                'nullable',
                'string',
            ],*/

           /* 'postal_code'           => [
                'nullable',
                'string',
            ],*/

           /* 'latitude'              => [
                'required',
                'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/',
            ],

            'longitude'  => [
                'required',
                'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/',
            ], */

             // 30 may 2021

            'last_name'  => [
                'required',
            ],

            'district'  => [
                'required',
            ],

            'home_no'  => [
                'required',
            ],

            'floor_no'  => [
                'required',
            ],

            'apartment_no'  => [
                'required',
            ],

            'telephone_no'  => [
                'nullable',
                //'numeric',
               // 'min:7',
               // 'max:15',
            ],

            'nearest_milestone'  => [
                'required',
            ],

            'notices'  => [
                'nullable',
            ],
        ];
    }
}
