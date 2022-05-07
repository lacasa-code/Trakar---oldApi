<?php

namespace App\Http\Requests\Api\V1\Vendor\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class VendorApproveStaffApiRequest extends FormRequest
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
            
            'staff_id' => [
                'required',
                'integer',
               'exists:users,id,deleted_at,NULL' // adited validation ahmed
               //'exists:vendorstaffs,id,deleted_at,NULL' // adited validation ahmed
            ],
        ];
    }
}
