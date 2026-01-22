import { NavLink, Link, useSearchParams, Form } from "react-router-dom";
import logo from "../assets/CPL_left.png";

export function Header() {
  const [searchParams] = useSearchParams();
  //   const search = useRef<HTMLInputElement>(null);

  const searchOnChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    console.log("Search input changed:", event.target.value);
  };

  return (
    <header className="cpl-header">
      <Link to="">
        <img src={logo} alt="Logo" className="inline-block h-20" />
      </Link>

      <Form className="search-form" action="/products">
        {/* onSubmit={handleSearchSubmit} */}

        <input
          type="search"
          name="search"
          onChange={searchOnChange}
          placeholder="Search"
          defaultValue={searchParams.get("search") ?? ""}
          className="search-input"
        />
      </Form>

      <nav>
        <NavLink
          to="/"
          className={({ isActive }) =>
            `text-white no-underline p-1 pb-0.5 border-solid border-b-2 ${
              isActive ? "border-green-500" : "border-transparent"
            }`
          }
        >
          Home
        </NavLink>
        <NavLink
          to="/upload"
          className={({ isActive }) =>
            `text-white no-underline p-1 pb-0.5 border-solid border-b-2 ${
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
