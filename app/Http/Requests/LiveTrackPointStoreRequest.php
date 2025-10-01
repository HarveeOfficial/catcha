<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LiveTrackPointStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Write permission is checked via write key in controller
    }

    public function rules(): array
    {
        return [
            'lat' => ['required','numeric','between:-90,90'],
            'lng' => ['required','numeric','between:-180,180'],
            'accuracy' => ['nullable','numeric','min:0'],
            'speed' => ['nullable','numeric','min:0'],
            'bearing' => ['nullable','numeric','between:0,360'],
            't' => ['nullable','date'],
        ];
    }
}
