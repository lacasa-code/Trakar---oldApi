<?php

namespace App\Http\Requests\Api\V1\Vendor\Staff;

use Illuminate\Foundation\Http\FormRequest;

class VendorAssignStoresStaffApiRequest extends FormRequest
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
            
            'staff_id'    => [
                'required',
                'integer',
                'exists:vendorstaffs,id,deleted_at,NULL' // adited validation ahmed

            ],

            'stores'    => [
                'required',
                // Rule::in('Manager', 'Staff'),
            ],
        ];
    }
}