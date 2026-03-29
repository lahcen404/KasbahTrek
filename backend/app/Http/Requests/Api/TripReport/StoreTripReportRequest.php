<?php

namespace App\Http\Requests\Api\TripReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tour_id' => 'required|exists:tours,id',
            'reason'  => 'required|string|min:10|max:1000',
        ];
    }
}
