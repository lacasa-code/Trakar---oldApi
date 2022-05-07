<?php

namespace App\Http\Requests\Admin\CarTypes;

use Illuminate\Foundation\Http\FormRequest;
use Gate;

class MassDestroyCarTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('car_type_delete');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids'   => 'required',
            // 'ids.*' => 'exists:car_mades,id',
        ];
    }
}
