<?php

namespace App\Http\Requests;

class FileManipulationRequest extends AbstractRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'from' => ['required', 'string'],
            'to' => ['required', 'string']
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
            'from' => $this->request->get('from') !== DIRECTORY_SEPARATOR ? $this->request->get('from') : null
        ]);
    }
}
