<?php

namespace App\Http\Requests;

use App\Models\CarYear;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyCarYearRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('car_year_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required',
           // 'ids.*' => 'exists:car_years,id',
        ];
    }
}
