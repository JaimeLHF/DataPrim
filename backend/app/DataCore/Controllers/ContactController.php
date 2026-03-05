<?php

namespace App\DataCore\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataCore\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Contatos
 *
 * Gestão de contatos de fornecedores com análise de prazos de pagamento vs mercado.
 */
class ContactController extends Controller
{
    /**
     * Listar contatos
     *
     * Retorna fornecedores com dados de contato, categorias, prazos de pagamento e comparação com mercado.
     *
     * @queryParam search string Busca por nome, contato, e-mail ou região. Example: Madeiras
     * @queryParam region string Filtrar por região. Example: Sul
     *
     * @response 200 {"market_payment_terms":42,"data":[{"id":1,"name":"Fornecedor Exemplo","cnpj":"12345678000190","region":"Sul","state":"PR","contact_name":"João Silva","contact_email":"joao@fornecedor.com","contact_phone":"(41) 99999-0000","payment_terms":30,"market_payment_terms":42,"terms_delta":-12,"terms_status":"above_market","categories":["MDF","Ferragens"],"invoice_count":10,"total_purchased":50000.0}]}
     */
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::withCount('invoices')
            ->with(['invoices' => fn($q) => $q->with('items')]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('contact_name', 'like', "%{$s}%")
                    ->orWhere('contact_email', 'like', "%{$s}%")
                    ->orWhere('region', 'like', "%{$s}%");
            });
        }

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        $suppliers = $query->get();

        $marketPaymentTerms = 42;

        $result = $suppliers->map(function ($supplier) use ($marketPaymentTerms) {
            $categories = DB::table('invoice_items as ii')
                ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
                ->where('i.supplier_id', $supplier->id)
                ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
                ->select('ii.category', DB::raw('SUM(ii.total_price) as total'))
                ->groupBy('ii.category')
                ->orderByDesc('total')
                ->pluck('category');

            $ourTerms    = $supplier->payment_terms ?? 30;
            $termsDelta  = $ourTerms - $marketPaymentTerms;
            $termsStatus = $termsDelta > 5  ? 'below_market' :
                ($termsDelta < -5 ? 'above_market' : 'on_par');

            $totalPurchased = DB::table('invoices')
                ->where('supplier_id', $supplier->id)
                ->when(app()->has('current_company_id'), fn($q) => $q->where('company_id', app('current_company_id')))
                ->sum('total_value');

            return [
                'id'                  => $supplier->id,
                'name'                => $supplier->name,
                'cnpj'                => $supplier->cnpj,
                'region'              => $supplier->region,
                'state'               => $supplier->state,
                'contact_name'        => $supplier->contact_name,
                'contact_email'       => $supplier->contact_email,
                'contact_phone'       => $supplier->contact_phone,
                'payment_terms'       => $ourTerms,
                'market_payment_terms' => $marketPaymentTerms,
                'terms_delta'         => $termsDelta,
                'terms_status'        => $termsStatus,
                'categories'          => $categories->values(),
                'invoice_count'       => $supplier->invoices_count,
                'total_purchased'     => round((float) $totalPurchased, 2),
            ];
        });

        return response()->json([
            'market_payment_terms' => $marketPaymentTerms,
            'data'                 => $result,
        ]);
    }
}
