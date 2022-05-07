<?php

namespace App\Http\Requests\Api\V1\Admin\Areas;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;

class UpdateAreaApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('area_update');
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
            
            'area_name' => [
                'required',
                'string',
               // 'unique:areas,area_name,NULL,id,deleted_at,NULL,'. $id,
                'unique:areas,area_name,'.$id.',id,deleted_at,NULL',
            ],

            'name_en' => [
                'required',
                'string',
               // 'unique:areas,area_name,NULL,id,deleted_at,NULL,'. $id,
                'unique:areas,name_en,'.$id.',id,deleted_at,NULL',
            ],

            'country_id' => [
                'required',
                'integer',
                'exists:countries,id,deleted_at,NULL' // adited validation ahmed
            ],

        ];
    }
}
