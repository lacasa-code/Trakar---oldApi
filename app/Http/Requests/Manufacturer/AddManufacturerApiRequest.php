<?php

namespace App\Http\Requests\Manufacturer;

use Illuminate\Foundation\Http\FormRequest;
use Gate;

class AddManufacturerApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('manufacturers_add');
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

            'manufacturer_name' => [
                'required',
                'string',
                'unique:manufacturers,manufacturer_name,NULL,id,deleted_at,NULL',
            ],

            'name_en' => [
                'required',
                'string',
                'unique:manufacturers,name_en,NULL,id,deleted_at,NULL',
            ],
            
        ];
    }
}
