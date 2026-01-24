import { lazy, Suspense } from "react";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import App from "./App";
import { NotFound } from "./pages/NotFound";

const Gallery = lazy(() => import("./pages/Gallery"));
const UploadPhoto = lazy(() => import("./pages/UploadPhoto"));
const PhotoIndividual = lazy(() => import("./pages/PhotoIndividual"));

const router = createBrowserRouter([
  {
    path: "/",
    element: <App />,
    errorElement: <NotFound />,
    children: [
      {
        index: true,
        element: (
          <Suspense fallback={<div>Loading...</div>}>
            <Gallery />
          </Suspense>
        ),
      },
      {
        path: "photo/:id",
        element: (
          <Suspense fallback={<div>Loading...</div>}>
            <PhotoIndividual />
          </Suspense>
        ),
      },
      {
        path: "upload",
        element: (
          <Suspense fallback={<div>Loading...</div>}>
            <UploadPhoto />
          </Suspense>
        ),
      },
    ],
  },
  {
    path: "*",
    element: <NotFound />,
  },
]);

export function Routes() {
  return <RouterProvider router={router} />;
}
