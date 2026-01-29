import {
  NavLink,
  Link,
  useSearchParams,
  Form,
  useNavigate,
} from "react-router-dom";
import logo from "../assets/CPL_left.png";
import { useRef } from "react";
import YearSelect from "./YearSelect";

export function Header() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const searchInputRef = useRef<HTMLInputElement>(null);
  const yearSelectRef = useRef<HTMLSelectElement>(null);

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    const searchValue = searchInputRef.current?.value || "";
    const yearValue = yearSelectRef.current?.value || "";

    const params = new URLSearchParams();
    if (searchValue) params.set("search", searchValue);
    if (yearValue) params.set("year", yearValue);

    // Navigate to current path with new search params
    navigate(`?${params.toString()}`, { replace: true });
  };

  return (
    <header className="cpl-header">
      <Link to="">
        <img src={logo} alt="Logo" className="h-20" />
      </Link>

      <Form className="search-form" onSubmit={handleSearchSubmit}>
        <input
          ref={searchInputRef}
          type="search"
          name="search"
          placeholder="Search"
          defaultValue={searchParams.get("search") ?? ""}
          className="search-input"
        />

        <YearSelect
          targetYear={2017}
          value={(() => {
            const yearParam = searchParams.get("year");
            if (!yearParam) return undefined;
            const yearNum = Number(yearParam);
            return isNaN(yearNum) ? undefined : yearNum;
          })()}
          onChange={(year) => {
            const params = new URLSearchParams(searchParams);
            if (year !== null) {
              params.set("year", year.toString());
            } else {
              params.delete("year");
            }
            navigate(`?${params.toString()}`, { replace: true });
          }}
        />

        <button type="submit" className="search-button">
          Search
        </button>
      </Form>

      <nav>
        <NavLink
          to="/"
          className={({ isActive }) =>
            `text-grey-400 hover:no-underline no-underline p-1 pb-0.5 border-solid border-b-2 ${
              isActive ? "border-green-500" : "border-transparent"
            }`
          }
        >
          Home
        </NavLink>
        <NavLink
          to="/upload"
          className={({ isActive }) =>
            `text-grey-400 hover:no-underline no-underline p-1 pb-0.5 border-solid border-b-2 ${
              isActive ? "border-green-500" : "border-transparent"
            }`
          }
        >
          Upload
        </NavLink>
      </nav>
    </header>
  );
}
