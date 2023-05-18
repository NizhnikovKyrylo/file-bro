<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FolderPathRequest extends AbstractRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'path' => ['required', 'string']
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'path' => $this->request->get('path', DIRECTORY_SEPARATOR)
        ]);
    }
}
