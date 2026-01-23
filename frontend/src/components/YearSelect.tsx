import { yearOptionsDescending } from "../utils/selectYears";

export default function YearSelect({
  targetYear,
  value,
  onChange,
}: {
  targetYear: number;
  value?: number;
  onChange: (v: number | null) => void;
}) {
  const options = yearOptionsDescending(targetYear);

  return (
    <select
      value={value ?? ""}
      onChange={(e) => {
        const selectedValue = e.target.value;
        onChange(selectedValue === "" ? null : Number(selectedValue));
      }}
      aria-label="Year"
      className="year-select"
    >
      <option value="">All years</option>
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  );
}
