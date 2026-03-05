<?php

namespace App\DataCore\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NfeImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'xml_file' => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if ($extension !== 'xml') {
                        $fail('O arquivo deve estar no formato XML.');
                        return;
                    }

                    $content = file_get_contents($value->getPathname());
                    $prev = libxml_use_internal_errors(true);
                    $parsed = simplexml_load_string($content);
                    libxml_clear_errors();
                    libxml_use_internal_errors($prev);

                    if ($parsed === false) {
                        $fail('O arquivo deve estar no formato XML.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'xml_file.required' => 'O arquivo XML é obrigatório.',
            'xml_file.file'     => 'O campo deve ser um arquivo.',
            'xml_file.max'      => 'O arquivo não pode ser maior que 10MB.',
        ];
    }
}
