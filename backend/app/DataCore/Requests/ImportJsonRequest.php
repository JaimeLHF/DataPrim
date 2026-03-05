<?php

namespace App\DataCore\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportJsonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source'                          => 'required|string|max:100',
            'invoices'                        => 'required|array|min:1',
            'invoices.*.invoice_number'       => 'required|string|max:255',
            'invoices.*.issue_date'           => 'required|date',
            'invoices.*.delivery_date'        => 'nullable|date',
            'invoices.*.payment_terms'        => 'nullable|integer|min:0',
            'invoices.*.supplier'             => 'required|array',
            'invoices.*.supplier.cnpj'        => 'required|string',
            'invoices.*.supplier.name'        => 'required|string|max:255',
            'invoices.*.supplier.state'       => 'nullable|string|max:2',
            'invoices.*.items'                => 'required|array|min:1',
            'invoices.*.items.*.description'  => 'required|string|max:500',
            'invoices.*.items.*.quantity'     => 'required|numeric|min:0.01',
            'invoices.*.items.*.unit_price'   => 'required|numeric|min:0',
            'invoices.*.items.*.category'     => 'nullable|string|max:100',
            'invoices.*.totals'               => 'required|array',
            'invoices.*.totals.total'         => 'required|numeric|min:0',
            'invoices.*.totals.goods'         => 'nullable|numeric|min:0',
            'invoices.*.totals.freight'       => 'nullable|numeric|min:0',
            'invoices.*.totals.tax'           => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'source.required'                        => 'O campo source (sistema de origem) é obrigatório.',
            'invoices.required'                      => 'O array de notas fiscais é obrigatório.',
            'invoices.min'                           => 'Envie pelo menos uma nota fiscal.',
            'invoices.*.invoice_number.required'     => 'Número da nota fiscal é obrigatório.',
            'invoices.*.issue_date.required'         => 'Data de emissão é obrigatória.',
            'invoices.*.supplier.cnpj.required'      => 'CNPJ do fornecedor é obrigatório.',
            'invoices.*.supplier.name.required'      => 'Nome do fornecedor é obrigatório.',
            'invoices.*.items.required'              => 'Lista de itens é obrigatória.',
            'invoices.*.items.min'                   => 'Cada nota deve ter pelo menos um item.',
            'invoices.*.items.*.quantity.min'         => 'Quantidade deve ser maior que zero.',
            'invoices.*.totals.total.required'       => 'Valor total é obrigatório.',
        ];
    }
}
