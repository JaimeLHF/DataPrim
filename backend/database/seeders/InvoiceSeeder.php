<?php

namespace Database\Seeders;

use App\DataCore\Models\Company;
use App\DataCore\Models\Invoice;
use App\DataCore\Models\InvoiceItem;
use App\DataCore\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    private array $categories = [
        'MDF'        => ['Chapa MDF 15mm', 'Chapa MDF 18mm', 'MDF Cru 6mm', 'Painel MDF Revestido', 'MDF BP Branco'],
        'Ferragens'  => ['Dobradiça Caneco 35mm', 'Parafuso Chipboard 4x40', 'Puxador Inox 128mm', 'Corrediça Telescópica 45cm', 'Minifix Zamak'],
        'Químicos'   => ['Cola PVA 23kg', 'Tinta Primer Madeira', 'Massa de Vedação 1L', 'Selador Nitrocelulose', 'Adesivo de Contato 4,5L'],
        'Aramados'   => ['Arame Galvanizado 12', 'Tela Aramada 50x50', 'Arame Recozido kg', 'Grade Aramada 60cm', 'Suporte Aramado Branco'],
        'Embalagens' => ['Caixa Papelão Duplex A4', 'Embalagem Plástica 30x40', 'Fita Adesiva Kraft 50m', 'Stretch Film 500m', 'Etiqueta Adesiva Rolo'],
        'Acessórios' => ['Rodízio Giratório 50mm', 'Régua Perfil Alumínio', 'Acabamento ABS Borda', 'Pé Nivelador Plástico', 'Perfil Cromado 3m'],
    ];

    private array $basePrices = [
        'MDF'        => [65.0,  95.0],
        'Ferragens'  => [1.50,  48.0],
        'Químicos'   => [35.0,  120.0],
        'Aramados'   => [8.0,   42.0],
        'Embalagens' => [3.50,  28.0],
        'Acessórios' => [4.0,   65.0],
    ];

    /**
     * Perfil de volume por empresa (slug => [meses_atras, min_nfs_mes, max_nfs_mes]).
     */
    /**
     * Apenas empresas fictícias — Móveis Ruiz terá dados reais via sync com Bling.
     */
    private array $companyProfiles = [
        'empresa-x'      => ['months' =>  8, 'min_invoices' => 3, 'max_invoices' => 6],
        'madeiras-silva'  => ['months' =>  6, 'min_invoices' => 2, 'max_invoices' => 4],
        'design-moveis'  => ['months' => 10, 'min_invoices' => 3, 'max_invoices' => 5],
        'movelar-br'     => ['months' =>  4, 'min_invoices' => 2, 'max_invoices' => 3],
    ];

    public function run(): void
    {
        $today = Carbon::now();

        foreach ($this->companyProfiles as $slug => $profile) {
            $company = Company::where('slug', $slug)->first();
            if (!$company) {
                continue;
            }

            // Buscar suppliers da empresa (filtro manual, sem global scope no seeder)
            $suppliers = Supplier::where('company_id', $company->id)->get();
            if ($suppliers->isEmpty()) {
                continue;
            }

            $invoiceCounter = 1000;

            for ($m = $profile['months'] - 1; $m >= 0; $m--) {
                $month = $today->copy()->subMonths($m);
                $invoicesInMonth = rand($profile['min_invoices'], $profile['max_invoices']);

                for ($i = 0; $i < $invoicesInMonth; $i++) {
                    $supplier     = $suppliers->random();
                    $issueDay     = rand(1, min(28, $month->daysInMonth));
                    $issueDate    = $month->copy()->day($issueDay);
                    $freightValue = round(rand(80, 320) + (rand(0, 99) / 100), 2);
                    $taxValue     = 0;

                    $invoiceNumber = 'NF-' . $company->id . '-' . str_pad(++$invoiceCounter, 6, '0', STR_PAD_LEFT);

                    // 2-4 categorias por nota
                    $allCats            = array_keys($this->categories);
                    shuffle($allCats);
                    $selectedCategories = array_slice($allCats, 0, rand(2, 4));

                    $totalValue = 0;
                    $items      = [];

                    foreach ($selectedCategories as $cat) {
                        $products  = $this->categories[$cat];
                        $priceBase = $this->basePrices[$cat];
                        $product   = $products[array_rand($products)];

                        $monthFactor = 1 + (($m - 6) * 0.004);
                        $noise       = 1 + ((rand(-50, 80) / 1000));
                        $unitPrice   = round(
                            (rand((int)($priceBase[0] * 100), (int)($priceBase[1] * 100)) / 100) * $monthFactor * $noise,
                            2
                        );
                        $qty        = round(rand(5, 100) + (rand(0, 99) / 100), 2);
                        $totalPrice = round($unitPrice * $qty, 2);

                        $taxValue   += round($totalPrice * 0.085, 2);
                        $totalValue += $totalPrice;

                        $items[] = [
                            'category'            => $cat,
                            'product_description' => $product,
                            'quantity'            => $qty,
                            'unit_price'          => $unitPrice,
                            'total_price'         => $totalPrice,
                        ];
                    }

                    $paymentTerms = [28, 30, 30, 45, 45, 60][array_rand([28, 30, 30, 45, 45, 60])];
                    $leadTimeDays = rand(5, 15);

                    $invoice = Invoice::create([
                        'company_id'     => $company->id,
                        'supplier_id'    => $supplier->id,
                        'invoice_number' => $invoiceNumber,
                        'issue_date'     => $issueDate->format('Y-m-d'),
                        'delivery_date'  => $issueDate->copy()->addDays($leadTimeDays)->format('Y-m-d'),
                        'total_value'    => round($totalValue, 2),
                        'freight_value'  => $freightValue,
                        'tax_value'      => round($taxValue, 2),
                        'payment_terms'  => $paymentTerms,
                    ]);

                    foreach ($items as $item) {
                        InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
                    }
                }
            }

            $count = Invoice::where('company_id', $company->id)->count();
            $this->command->info("  {$company->name}: {$count} notas fiscais geradas");
        }
    }
}
