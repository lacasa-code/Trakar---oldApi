<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportApiRequest extends FormRequest
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

            'start_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                //'date_format:Y-m-d H:i:s',
                'before_or_equal:end_date',
            ],
            
            'end_date'   => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
        
        ];
    }
}
 