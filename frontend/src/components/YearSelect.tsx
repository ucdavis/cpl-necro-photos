import { yearOptionsDescending } from "../utils/selectYears";

export default function YearSelect({
  targetYear,
  className,
  value,
  showAllOption = true,
  onChange,
}: {
  targetYear?: number | null;
  className?: string;
  value?: number;
  showAllOption?: boolean;
  onChange: (v: number | null) => void;
}) {
  const options = yearOptionsDescending(targetYear ?? new Date().getFullYear());

  return (
    <select
      value={value ?? ""}
      onChange={(e) => {
        const selectedValue = e.target.value;
        onChange(selectedValue === "" ? null : Number(selectedValue));
      }}
      aria-label="Year"
      className={`${className ?? "year-select"}`}
    >
      {showAllOption && <option value="">All years</option>}
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  );
}
