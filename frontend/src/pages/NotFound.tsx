import { useRouteError } from "react-router-dom";

export function NotFound() {
  const error = useRouteError();
  console.error(error);
  return (
    <div>
      <h2>404 - Page Not Found</h2>
      <p>The page you are looking for does not exist.</p>
    </div>
  );
}
