<?php

namespace App\Http\Requests\Api\Tour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'difficulty' => 'sometimes|in:EASY,MEDIUM,HARD',
            'max_spots' => 'sometimes|integer|min:1',
            'duration_hours' => 'sometimes|nullable|integer|min:1|max:720',
            'date' => 'sometimes|nullable|date|after_or_equal:today',
            'category_id' => 'sometimes|nullable|exists:categories,id',
        ];
    }
}
