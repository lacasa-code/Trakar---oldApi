<?php

namespace App\Http\Requests;

use App\Models\CarYear;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateCarYearRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('car_year_edit');
    }

    public function rules()
    {
        $id = request()->route('id');
        return [
            'year' => [
                'required',
                'integer',
                'min:1990',
                'max:'.(date('Y') + 1),
                'unique:car_years,year,'. $id,
                //'unique:car_years,year,'. request()->route('carYear')->id,
            ],
        ];
    }
}
