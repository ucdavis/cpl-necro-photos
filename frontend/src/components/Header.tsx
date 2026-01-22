import { NavLink, Link, useSearchParams, Form } from "react-router-dom";
import logo from "../assets/react.svg";
import { useRef } from "react";

export function Header() {
  const [searchParams] = useSearchParams();
  //   const search = useRef<HTMLInputElement>(null);

  const searchOnChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    console.log("Search input changed:", event.target.value);
  };

  return (
    <header className="text-center text-slate-50 bg-slate-900 h-40 p-5">
      {/* https://reactrouter.com/en/main/components/form */}

      <Link to="">
        <img src={logo} alt="Logo" className="inline-block h-20" />
      </Link>

      <Form className="relative text-right" action="/products">
        {/* onSubmit={handleSearchSubmit} */}

        <input
          type="search"
          name="search"
          onChange={searchOnChange}
          placeholder="Search"
          defaultValue={searchParams.get("search") ?? ""}
          className="absolute right-0 top-0 rounded py-2 px-3 text-gray-700"
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
          Products
        </NavLink>
      </nav>
    </header>
  );
}
