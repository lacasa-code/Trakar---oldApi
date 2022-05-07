<?php

namespace App\Http\Requests\Admin\CarTypes;

use Illuminate\Foundation\Http\FormRequest;
use Gate;

class StoreCarTypeApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('car_type_add');
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

            'type_name' => [
                'required',
                'string',
                'unique:cartypes,type_name,NULL,id,deleted_at,NULL',
            ],

            'photo' => [
                'required',
                //'file',
                'image'
            ],

            'name_en'      => [
                'required',
                'string',
            ],

        ];
    }
}
