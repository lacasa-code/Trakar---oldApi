<?php

namespace App\Http\Requests\Manufacturer;

use Illuminate\Foundation\Http\FormRequest;
use Gate;

class UpdateManufacturerApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('manufacturers_update');
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
            'manufacturer_name' => [
                'required',
                'string',
                'unique:manufacturers,manufacturer_name,'. $id,
            ],

            'name_en' => [
                'required',
                'string',
                'unique:manufacturers,name_en,'. $id,
            ],

        ];
    }
}
