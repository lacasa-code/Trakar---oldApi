<?php

namespace App\Http\Requests\Website\User\Ads;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UpdateAdvertisementApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('advertisements_edit');
        // return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = request()->route('id');

            return [

            'ad_name' => [
                'required', 
                'string', 
               // 'unique:advertisements,ad_name,'. $id,
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
                'unique:advertisements,ad_url,'. $id,
            ],

            'platform' => [
                'required', 
                Rule::in('web','mobile'),
            ],

            'ad_image' => [
                'nullable', 
            ],

        ];
    }
}
