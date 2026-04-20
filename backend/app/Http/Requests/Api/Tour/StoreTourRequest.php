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
            'difficulty' => 'required|in:EASY,MEDIUM,HARD',
            'max_spots' => 'required|integer|min:1',
            'duration_hours' => 'nullable|integer|min:1|max:720',
            'date' => 'nullable|date|after_or_equal:today',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
