<?php

namespace App\Http\Requests\Api\V1\Vendor\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class VendorAddStaffApiRequest extends FormRequest
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
            
            'email'    => [
                'required',
                'email',
                'regex:/(.+)@(.+)\.(.+)/i',
                'unique:users,email,NULL,id,deleted_at,NULL',
                'unique:vendorstaffs,email,NULL,id,deleted_at,NULL',

            ],

            'role'    => [
                // Rule::in('Manager', 'Staff'),
                'required',
                'integer',
                'exists:roles,id,deleted_at,NULL' // adited validation ahmed
            ],

            'stores'    => [
                'required',
                // Rule::in('Manager', 'Staff'),
            ],
        ];
    }
}
