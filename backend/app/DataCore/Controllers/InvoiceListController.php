<?php

namespace App\DataCore\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataCore\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Notas Fiscais
 *
 * Listagem, consulta e exclusão de notas fiscais importadas.
 */
class InvoiceListController extends Controller
{
    /**
     * Listar notas fiscais
     *
     * Retorna notas fiscais paginadas com filtros opcionais.
     *
     * @queryParam supplier_id integer Filtrar por fornecedor. Example: 1
     * @queryParam start_date string Data inicial (YYYY-MM-DD). Example: 2026-01-01
     * @queryParam end_date string Data final (YYYY-MM-DD). Example: 2026-12-31
     * @queryParam category string Filtrar por categoria de item. Example: MDF
     * @queryParam search string Busca por número da nota ou nome do fornecedor. Example: NF-001
     * @queryParam per_page integer Itens por página (padrão: 15). Example: 15
     *
     * @response 200 {"data":[{"id":1,"invoice_number":"NF-001","issue_date":"15/01/2026","supplier":"Fornecedor Exemplo","supplier_id":1,"total_value":5136.75,"freight_value":200.0,"tax_value":386.75,"items_count":2,"categories":["MDF","Ferragens"]}],"meta":{"current_page":1,"last_page":1,"per_page":15,"total":1}}
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['supplier', 'items'])
            ->orderBy('issue_date', 'desc');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('start_date')) {
            $query->where('issue_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('issue_date', '<=', $request->end_date);
        }

        if ($request->filled('category')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $perPage = (int) $request->get('per_page', 15);
        $invoices = $query->paginate($perPage);

        return response()->json([
            'data' => $invoices->getCollection()->map(fn($inv) => [
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'issue_date'     => $inv->issue_date->format('d/m/Y'),
                'supplier'       => $inv->supplier?->name ?? '—',
                'supplier_id'    => $inv->supplier_id,
                'total_value'    => (float) $inv->total_value,
                'freight_value'  => (float) $inv->freight_value,
                'tax_value'      => (float) $inv->tax_value,
                'items_count'    => $inv->items->count(),
                'categories'     => $inv->items->pluck('category')->unique()->values(),
            ]),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
                'per_page'     => $invoices->perPage(),
                'total'        => $invoices->total(),
            ],
        ]);
    }

    /**
     * Detalhe de nota fiscal
     *
     * Retorna dados completos de uma nota fiscal com seus itens.
     *
     * @urlParam id integer required ID da nota fiscal. Example: 1
     *
     * @response 200 {"id":1,"invoice_number":"NF-001","issue_date":"15/01/2026","supplier":"Fornecedor Exemplo","total_value":5136.75,"freight_value":200.0,"tax_value":386.75,"items":[{"id":1,"category":"MDF","product_description":"MDF 18mm Branco","quantity":100,"unit_price":45.5,"total_price":4550.0}]}
     * @response 404 {"message":"No query results for model [App\\Models\\Invoice] 999"}
     */
    public function show(int $id): JsonResponse
    {
        $invoice = Invoice::with(['supplier', 'items'])->findOrFail($id);

        return response()->json([
            'id'             => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'issue_date'     => $invoice->issue_date->format('d/m/Y'),
            'supplier'       => $invoice->supplier?->name ?? '—',
            'total_value'    => (float) $invoice->total_value,
            'freight_value'  => (float) $invoice->freight_value,
            'tax_value'      => (float) $invoice->tax_value,
            'items'          => $invoice->items->map(fn($item) => [
                'id'                  => $item->id,
                'category'            => $item->category,
                'product_description' => $item->product_description,
                'quantity'            => (float) $item->quantity,
                'unit_price'          => (float) $item->unit_price,
                'total_price'         => (float) $item->total_price,
            ]),
        ]);
    }

    /**
     * Excluir nota fiscal
     *
     * Remove a nota fiscal e todos os seus itens.
     *
     * @urlParam id integer required ID da nota fiscal. Example: 1
     *
     * @response 200 {"message":"Nota fiscal excluída com sucesso."}
     */
    public function destroy(int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->items()->delete();
        $invoice->delete();

        return response()->json(['message' => 'Nota fiscal excluída com sucesso.']);
    }
}
