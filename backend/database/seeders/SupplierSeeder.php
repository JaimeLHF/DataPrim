<?php

namespace Database\Seeders;

use App\DataCore\Models\Company;
use App\DataCore\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Pool de fornecedores do setor moveleiro.
     * Cada empresa recebe um subconjunto desse pool (com company_id proprio).
     */
    private array $supplierPool = [
        ['name' => 'Madeireira São Paulo Ltda',        'cnpj' => '12345678000101', 'region' => 'Sudeste', 'state' => 'SP', 'contact_name' => 'Carlos Alberto Ramos',  'contact_email' => 'c.ramos@madeirasaopaulo.com.br',    'contact_phone' => '(11) 9 8765-4321', 'payment_terms' => 30],
        ['name' => 'Ferragens do Sul Distribuidora',    'cnpj' => '12345678000102', 'region' => 'Sul',     'state' => 'SC', 'contact_name' => 'Fernanda Oliveira',     'contact_email' => 'fernanda@ferragenssul.com.br',      'contact_phone' => '(51) 9 9988-7766', 'payment_terms' => 45],
        ['name' => 'Química Industrial Norte',          'cnpj' => '12345678000103', 'region' => 'Norte',   'state' => 'PA', 'contact_name' => 'Breno Tavares',         'contact_email' => 'breno.tavares@quimicanorte.ind.br', 'contact_phone' => '(92) 9 8811-3344', 'payment_terms' => 28],
        ['name' => 'Embalagex Comércio e Serviços',     'cnpj' => '12345678000104', 'region' => 'Centro-Oeste', 'state' => 'GO', 'contact_name' => 'Patrícia Melo',    'contact_email' => 'pmelo@embalagex.com.br',            'contact_phone' => '(62) 9 7799-0011', 'payment_terms' => 60],
        ['name' => 'Acessórios & Acabamentos Brasil',   'cnpj' => '12345678000105', 'region' => 'Nordeste','state' => 'BA', 'contact_name' => 'Ricardo Gomes',         'contact_email' => 'r.gomes@aabrasil.com.br',           'contact_phone' => '(71) 9 9922-5566', 'payment_terms' => 45],
        ['name' => 'MDF Center Distribuidora',          'cnpj' => '12345678000106', 'region' => 'Sudeste', 'state' => 'MG', 'contact_name' => 'Ana Luiza Costa',       'contact_email' => 'ana@mdfcenter.com.br',              'contact_phone' => '(31) 9 8877-1122', 'payment_terms' => 30],
        ['name' => 'Parafusos & Cia Ltda',              'cnpj' => '12345678000107', 'region' => 'Sul',     'state' => 'PR', 'contact_name' => 'Marcos Vieira',         'contact_email' => 'marcos@parafusoecia.com.br',        'contact_phone' => '(41) 9 9966-3344', 'payment_terms' => 30],
        ['name' => 'ColaBras Indústria Química',        'cnpj' => '12345678000108', 'region' => 'Sudeste', 'state' => 'SP', 'contact_name' => 'Juliana Santos',        'contact_email' => 'juliana@colabras.com.br',           'contact_phone' => '(11) 9 7755-8899', 'payment_terms' => 45],
        ['name' => 'Aramados Paraná S.A.',              'cnpj' => '12345678000109', 'region' => 'Sul',     'state' => 'PR', 'contact_name' => 'Roberto Mendes',        'contact_email' => 'roberto@aramadospr.com.br',         'contact_phone' => '(43) 9 8833-2211', 'payment_terms' => 28],
        ['name' => 'EmbalaMax Soluções',                'cnpj' => '12345678000110', 'region' => 'Sul',     'state' => 'SC', 'contact_name' => 'Camila Ferreira',       'contact_email' => 'camila@embalamax.com.br',           'contact_phone' => '(47) 9 9944-5566', 'payment_terms' => 30],
    ];

    /**
     * Indices do pool para cada empresa (por slug).
     */
    /**
     * Apenas empresas fictícias — Móveis Ruiz terá dados reais via sync com Bling.
     */
    private array $companySuppliers = [
        'empresa-x'      => [0, 5, 1, 7],           // 4 suppliers
        'madeiras-silva'  => [5, 6, 9],              // 3 suppliers
        'design-moveis'  => [0, 7, 8],              // 3 suppliers
        'movelar-br'     => [3, 4, 6],              // 3 suppliers
    ];

    public function run(): void
    {
        $companies = Company::whereIn('slug', array_keys($this->companySuppliers))->get()->keyBy('slug');

        foreach ($this->companySuppliers as $slug => $indices) {
            $company = $companies->get($slug);
            if (!$company) {
                continue;
            }

            foreach ($indices as $idx) {
                $data = $this->supplierPool[$idx];
                Supplier::create(array_merge($data, ['company_id' => $company->id]));
            }
        }
    }
}
