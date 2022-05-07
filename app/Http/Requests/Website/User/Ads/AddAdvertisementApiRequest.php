<?php

namespace App\Http\Requests\Website\User\Ads;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AddAdvertisementApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('advertisements_add');
        // return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'ad_name' => [
                'required', 
                'string',
               // 'unique:advertisements,ad_name',
            ],

            'ad_position' => [
                'required', 
                'integer',
                'exists:adpositions,id,deleted_at,NULL' // adited validation ahmed
            ],

            'cartype_id' => [
                'required', 
                'integer',
                'exists:cartypes,id,deleted_at,NULL' // adited validation ahmed
            ],

            'ad_url' => [
                'required', 
                'url',
            ],

            'ad_image' => [
                'nullable', 
            ],

            'platform' => [
                'required', 
                Rule::in('web','mobile'),
            ],

            'photo'         => [
                'required',
                'file',
                'image'
                 // 'mimes:jpg,jpeg,gif,png',
            ],

        ];
    }
}
