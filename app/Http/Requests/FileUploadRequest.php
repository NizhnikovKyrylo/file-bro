<?php

namespace App\Http\Requests;

class FileUploadRequest extends AbstractRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'path' => ['required', 'string'],
            'name' => ['nullable', 'string'],
            'file' => ['required', 'file']
        ];
    }
}
