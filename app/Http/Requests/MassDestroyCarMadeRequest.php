<?php

namespace App\Http\Requests;

use App\Models\CarMade;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyCarMadeRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('car_made_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required',
            // 'ids.*' => 'exists:car_mades,id',
        ];
    }
}
