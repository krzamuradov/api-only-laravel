<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'string', 'in:title,-title,created_at,-created_at'],
        ];
    }

    /**
     * @return array{per_page:int,search:?string,sort:string}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'per_page' => (int) ($validated['per_page'] ?? 15),
            'search' => $validated['search'] ?? null,
            'sort' => $validated['sort'] ?? '-created_at',
        ];
    }
}
