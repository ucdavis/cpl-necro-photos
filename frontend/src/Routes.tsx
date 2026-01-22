import { lazy, Suspense } from "react";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import App from "./App";
import { NotFound } from "./pages/NotFound";

const UploadPhoto = lazy(() => import("./pages/UploadPhoto"));

const router = createBrowserRouter([
  {
    path: "/",
    element: <App />,
    errorElement: <NotFound />,
  },
  {
    path: "/upload",
    element: (
      <Suspense fallback={<div>Loading...</div>}>
        <UploadPhoto />
      </Suspense>
    ),
  },
  {
    path: "*",
    element: <NotFound />,
  },
]);

export function Routes() {
  return <RouterProvider router={router} />;
}
