<?php

namespace App\Http\Requests\Api\V1\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TotalOrdersFilterApiRequest extends FormRequest
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
            
            'fetch'        => [
                'nullable',
                // Rule::in('pending','in progress', 'cancelled', 'delivered'),
                Rule::in('0','1', '2', '3'),
            ],

        ];
    }
}
