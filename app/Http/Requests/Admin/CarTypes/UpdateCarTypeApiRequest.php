<?php

namespace App\Http\Requests\Admin\CarTypes;

use Illuminate\Foundation\Http\FormRequest;
use Gate;

class UpdateCarTypeApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('car_type_update');
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

            'type_name' => [
                'required',
                'string',
                'unique:cartypes,type_name,'. $id,
            ],

            'name_en'      => [
                'required',
                'string',
                'unique:cartypes,name_en,'. $id,
            ],

        ];
    }
}
