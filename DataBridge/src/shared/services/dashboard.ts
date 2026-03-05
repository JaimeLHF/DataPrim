import api from './api';

// ── Existing types (re-exported) ──────────────────────────────────────────
export interface KpiData {
  overall_tco_average: number;
  market_variation_percent: number;
  freight_weight_percent: number;
  top_category: { category: string; total_value: number } | null;
  benchmark_by_category: BenchmarkRow[];
}

export interface BenchmarkRow {
  category: string;
  company_tco_avg: number;
  market_tco_avg: number;
  variation_percent: number;
  status: 'below_market' | 'above_market';
}

export interface PriceEvolutionData {
  data: Record<string, number | string>[];
  categories: string[];
  market_categories?: string[];
}

export interface TcoBreakdownRow {
  category: string;
  avg_unit_price: number;
  avg_freight: number;
  avg_tax: number;
  tco_total: number;
}

export interface TcoBreakdownData {
  company: TcoBreakdownRow[];
  market: TcoBreakdownRow[];
}

export interface GrossVsNetRow {
  category: string;
  gross_cost: number;
  tax_credit: number;
  net_cost: number;
  credit_percent: number;
}

export interface PriceIndexData {
  data: Record<string, number | string>[];
  categories: string[];
}

export interface DispersionRow {
  category: string;
  min_price: number;
  avg_price: number;
  max_price: number;
  range: number;
}

export interface FreightRow {
  category: string;
  total_invoiced: number;
  total_freight: number;
  freight_percent: number;
}

export interface RankingRow {
  rank: number;
  category: string;
  total_value: number;
  total_qty: number;
  items_count: number;
}

export interface ImportResult {
  success: boolean;
  message: string;
  invoice: {
    id: number;
    invoice_number: string;
    issue_date: string;
    supplier: string;
    total_value: number;
    freight_value: number;
    tax_value: number;
  };
  items_count: number;
  items: {
    category: string;
    product_description: string;
    quantity: number;
    unit_price: number;
    total_price: number;
  }[];
}

export interface AsyncImportResponse {
  message: string;
  ingestion_id: number;
  status: string;
}

export interface IngestionStatus {
  id: number;
  channel: string;
  source: string;
  status: 'pending' | 'processing' | 'done' | 'failed';
  attempts: number;
  error_message: string | null;
  created_at: string;
  processed_at: string | null;
}

// ── Invoice List types ────────────────────────────────────────────────────
export interface InvoiceRow {
  id: number;
  invoice_number: string;
  issue_date: string;
  supplier: string;
  supplier_id: number;
  total_value: number;
  freight_value: number;
  tax_value: number;
  items_count: number;
  categories: string[];
}

export interface PageMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface InvoiceDetail {
  id: number;
  invoice_number: string;
  issue_date: string;
  supplier: string;
  total_value: number;
  freight_value: number;
  tax_value: number;
  items: {
    id: number;
    category: string;
    product_description: string;
    quantity: number;
    unit_price: number;
    total_price: number;
  }[];
}

// ── Supplier types ────────────────────────────────────────────────────────
export interface SupplierRow {
  supplier_id: number;
  supplier_name: string;
  cnpj: string | null;
  region: string;
  state: string | null;
  invoice_count: number;
  total_purchased: number;
  avg_invoice_value: number;
  total_freight: number;
  avg_freight_pct: number;
  category_count: number;
  first_purchase: string;
  last_purchase: string;
  score: number;
  classification: 'Estratégico' | 'Alternativo' | 'Risco';
}

// ── Alert types ───────────────────────────────────────────────────────────
export interface Alert {
  type: 'price_anomaly' | 'high_freight' | 'consecutive_rise' | 'concentration_risk';
  severity: 'high' | 'medium' | 'low';
  title: string;
  message: string;
  detail: string;
  date: string;
}

export interface AlertsResponse {
  total: number;
  high: number;
  medium: number;
  alerts: Alert[];
}

// ── Fetch functions ───────────────────────────────────────────────────────
export async function fetchKpis(params?: Record<string, string>): Promise<KpiData> {
  return (await api.get('/dashboard/kpis', { params })).data;
}

export async function fetchPriceEvolution(params?: Record<string, string>): Promise<PriceEvolutionData> {
  return (await api.get('/dashboard/price-evolution', { params })).data;
}

export async function fetchDispersion(params?: Record<string, string>): Promise<{ data: DispersionRow[] }> {
  return (await api.get('/dashboard/dispersion', { params })).data;
}

export async function fetchFreightImpact(params?: Record<string, string>): Promise<{ data: FreightRow[]; overall_percent: number }> {
  return (await api.get('/dashboard/freight-impact', { params })).data;
}

export async function fetchCategoryRanking(params?: Record<string, string>): Promise<{ data: RankingRow[] }> {
  return (await api.get('/dashboard/category-ranking', { params })).data;
}

export async function fetchTcoBreakdown(params?: Record<string, string>): Promise<TcoBreakdownData> {
  return (await api.get('/dashboard/tco-breakdown', { params })).data;
}

export async function fetchGrossVsNet(params?: Record<string, string>): Promise<{ data: GrossVsNetRow[] }> {
  return (await api.get('/dashboard/gross-vs-net', { params })).data;
}

export async function fetchPriceIndex(params?: Record<string, string>): Promise<PriceIndexData> {
  return (await api.get('/dashboard/price-index', { params })).data;
}

export interface PreviewResult {
  invoice_number: string;
  issue_date: string;
  supplier: string;
  supplier_cnpj: string;
  supplier_region: string;
  supplier_state: string;
  total_value: number;
  freight_value: number;
  tax_value: number;
  items_count: number;
  items: {
    product_description: string;
    category: string;
    quantity: number;
    unit_price: number;
    total_price: number;
  }[];
}

export async function previewXml(file: File): Promise<PreviewResult> {
  const formData = new FormData();
  formData.append('xml_file', file);
  return (await api.post('/invoices/preview-xml', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })).data;
}

export async function importXml(file: File): Promise<AsyncImportResponse> {
  const formData = new FormData();
  formData.append('xml_file', file);
  return (await api.post('/invoices/import-xml', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })).data;
}

export async function checkIngestionStatus(id: number): Promise<IngestionStatus> {
  return (await api.get(`/ingestions/${id}/status`)).data;
}

// Invoices
export async function fetchInvoices(params?: Record<string, string>): Promise<{ data: InvoiceRow[]; meta: PageMeta }> {
  return (await api.get('/invoices', { params })).data;
}

export async function fetchInvoice(id: number): Promise<InvoiceDetail> {
  return (await api.get(`/invoices/${id}`)).data;
}

export async function deleteInvoice(id: number): Promise<void> {
  await api.delete(`/invoices/${id}`);
}

// Suppliers
export async function fetchSuppliers(params?: Record<string, string>): Promise<{ data: SupplierRow[] }> {
  return (await api.get('/suppliers', { params })).data;
}

// Alerts
export async function fetchAlerts(): Promise<AlertsResponse> {
  return (await api.get('/alerts')).data;
}

// ── Saving & Cost Avoidance ───────────────────────────────────────────────
export interface SavingCategory {
  category: string;
  prev_avg_price: number | null;
  curr_avg_price: number;
  price_change_pct: number;
  saving_abs: number;
  cost_avoid_abs: number;
  market_index_pct: number;
  hypothetical_avg: number | null;
  total_spend: number;
  status: 'saving' | 'stable' | 'overpaying';
}

export interface SavingData {
  total_saving: number;
  total_cost_avoid: number;
  period_label: string;
  categories: SavingCategory[];
}

export async function fetchSaving(): Promise<SavingData> {
  return (await api.get('/dashboard/saving')).data;
}

// ── Contacts ─────────────────────────────────────────────────────────────
export interface ContactRow {
  id: number;
  name: string;
  cnpj: string | null;
  region: string;
  state: string | null;
  contact_name: string | null;
  contact_email: string | null;
  contact_phone: string | null;
  payment_terms: number;
  market_payment_terms: number;
  terms_delta: number;
  terms_status: 'below_market' | 'on_par' | 'above_market';
  categories: string[];
  invoice_count: number;
  total_purchased: number;
}

export async function fetchContacts(params?: Record<string, string>): Promise<{ market_payment_terms: number; data: ContactRow[] }> {
  return (await api.get('/contacts', { params })).data;
}

// ── Cost Structure Benchmark ──────────────────────────────────────────────
export type DeltaStatus = 'above_market' | 'below_market' | 'aligned' | 'no_data';

export interface BenchmarkCategoryData {
  category_slug: string;
  category_name: string;
  company_spend: number;
  company_percentage: number;
  benchmark_percentage: number | null;
  benchmark_median: number | null;
  benchmark_p25: number | null;
  benchmark_p75: number | null;
  delta_percentage: number | null;
  delta_status: DeltaStatus;
  financial_impact: number;
  financial_impact_label: string;
}

export interface BenchmarkMeta {
  company_id: number;
  company_name: string;
  period: string;
  period_label: string;
  region: string;
  benchmark_region: string;
  benchmark_sample_size: number;
  benchmark_is_valid: boolean;
  total_company_spend: number;
  calculated_at: string | null;
}

export interface BenchmarkSummary {
  total_potential_saving: number;
  categories_above_market: number;
  categories_below_market: number;
  categories_aligned: number;
  worst_category: {
    slug: string;
    name: string;
    delta: number;
    impact: number;
  } | null;
}

export interface CostStructureBenchmarkData {
  meta: BenchmarkMeta;
  categories: BenchmarkCategoryData[];
  summary: BenchmarkSummary;
}

export interface BenchmarkPeriodOption {
  value: string;
  label: string;
}

export async function fetchCostStructureBenchmark(
  params?: Record<string, string>,
): Promise<CostStructureBenchmarkData> {
  return (await api.get('/dashboard/cost-structure-benchmark', { params })).data;
}

export async function fetchBenchmarkPeriods(
  params?: Record<string, string>,
): Promise<{ periods: BenchmarkPeriodOption[] }> {
  return (await api.get('/dashboard/cost-structure-benchmark/periods', { params })).data;
}

// ── Category Products (modal drill-down) ──────────────────────────────────

export interface CategoryProduct {
  id: number;
  product_description: string;
  quantity: number;
  unit_price: number;
  total_price: number;
  pct_of_category: number;
  pct_of_company: number;
  supplier_name: string;
  supplier_cnpj: string;
  invoice_number: string;
  issue_date: string;
  freight_allocated: number;
  tax_allocated: number;
  total_with_costs: number;
}

export interface CategoryProductsMeta {
  period: string;
  total_company_spend: number;
  products_count: number;
  unique_suppliers: number;
}

export interface CategoryInfo {
  slug: string;
  name: string;
  total_spend: number;
  company_percentage: number;
  benchmark_percentage: number | null;
  delta_percentage: number | null;
  delta_status: DeltaStatus;
}

export interface CategoryProductsData {
  category: CategoryInfo;
  meta: CategoryProductsMeta;
  products: CategoryProduct[];
}

export async function fetchCategoryProducts(
  params: Record<string, string>,
): Promise<CategoryProductsData> {
  return (await api.get('/dashboard/cost-structure-benchmark/category-products', { params })).data;
}

