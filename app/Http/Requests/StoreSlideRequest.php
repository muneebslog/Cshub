<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSlideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'html_content' => ['required', 'string', 'max:31457280'],
            'original_filename' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'html_content.required' => __('Please choose an HTML lesson file.'),
            'html_content.max' => __('The HTML file may not be larger than 30MB.'),
            'original_filename.required' => __('Please choose an HTML lesson file.'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $filename = (string) $this->input('original_filename', '');
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if ($filename !== '' && ! in_array($extension, ['html', 'htm'], true)) {
                $validator->errors()->add('original_filename', __('The file must be an .html or .htm document.'));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('category_id') === '' || $this->input('category_id') === null) {
            $this->merge(['category_id' => null]);
        }
    }
}
