<?php

namespace App\Http\Requests\Api\Tour;

use Illuminate\Foundation\Http\FormRequest;

class StoreTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string',
            'price' => 'required|numeric',
            'difficulty' => 'required|string',
            'max_spots' => 'required|integer|min:1',
            'duration_hours' => 'nullable|integer|min:1|max:720',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }
}
