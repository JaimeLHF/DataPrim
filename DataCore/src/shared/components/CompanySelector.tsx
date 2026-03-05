import { useCompany } from '../contexts/CompanyContext';

export default function CompanySelector() {
  const { companies, current, loading, switchCompany } = useCompany();

  if (loading) return null;
  if (companies.length <= 1 && current) return (
    <div className="company-selector-single">{current.name}</div>
  );

  return (
    <select
      className="company-selector"
      value={current?.id ?? ''}
      onChange={(e) => switchCompany(Number(e.target.value))}
    >
      {companies.map((c) => (
        <option key={c.id} value={c.id}>
          {c.name}
        </option>
      ))}
    </select>
  );
}
