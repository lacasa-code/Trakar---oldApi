<?php

namespace App\Http\Requests;

use App\Models\CarYear;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreCarYearRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('car_year_create');
    }

    public function rules()
    {
        return [
            'year' => [
                'integer',
                'min:1990',
                'max:'.(date('Y') + 1),
                'required',
                'unique:car_years,year,NULL,id,deleted_at,NULL',
            ],
        ];
    }
}
