/**
 * Select drop down years utility functions
 */

// returns [currentYear, currentYear-1, ..., targetYear] if target <= current
export function yearsDescending(targetYear: number): number[] {
  const current = new Date().getFullYear();
  const start = Math.max(targetYear, 0);
  const end = current;
  const length = Math.abs(end - start) + 1;
  return Array.from({ length }, (_, i) => end - i);
}

export type YearOption = { value: number; label: string };

export function yearOptionsDescending(targetYear: number): YearOption[] {
  return yearsDescending(targetYear).map((y) => ({
    value: y,
    label: String(y),
  }));
}
