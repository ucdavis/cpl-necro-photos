import { useState, useEffect } from "react";
import { useSearchParams } from "react-router-dom";
import { getPhotos } from "../services/photoService";
import { PhotoThumbnail } from "../components/PhotoThumbnail";
import { GalleryHeader } from "../components/GalleryHeader";
import type { Photo, PaginationInfo } from "../types";

export default function Gallery() {
  // URL search params for filters and pagination
  const [searchParams, setSearchParams] = useSearchParams();
  // state
  const [photos, setPhotos] = useState<Photo[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [perPage, setPerPage] = useState(20);
  const [currentPage, setCurrentPage] = useState(1);
  const [pagination, setPagination] = useState<PaginationInfo | null>(null);

  useEffect(() => {
    // Fetch photos whenever search params change
    async function fetchPhotos() {
      try {
        setLoading(true);

        // Get search and year from URL params
        const search = searchParams.get("search");
        const year = searchParams.get("year");

        // Get pagination settings from URL params with sensible defaults
        const perPageParam = searchParams.get("per_page");
        const pageParam = searchParams.get("page");

        const perPageValue = (() => {
          const parsed = perPageParam ? Number(perPageParam) : NaN;
          return Number.isFinite(parsed) && parsed > 0 ? parsed : 20;
        })();

        const pageValue = (() => {
          const parsed = pageParam ? Number(pageParam) : NaN;
          return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
        })();

        // If searching, don't filter by year (search all years)
        // If not searching, use year filter if provided
        const queryParams = new URLSearchParams();
        if (search) {
          queryParams.set("search", search);
        } else if (year) {
          queryParams.set("year", year);
        }
        queryParams.set("per_page", perPageValue.toString());
        queryParams.set("page", pageValue.toString());

        const result = await getPhotos(queryParams.toString());

        setPhotos(result?.data || []);
        setPagination(result?.pagination || null);
      } catch (err) {
        const errorMessage =
          err instanceof Error ? err.message : "An error occurred";
        setError(errorMessage);
      } finally {
        setLoading(false);
      }
    }

    fetchPhotos();
  }, [searchParams]);

  const handlePerPageChange = (newPerPage: number) => {
    setSearchParams((prev) => {
      const params = new URLSearchParams(prev);
      params.set("per_page", newPerPage.toString());
      // When per-page changes, reset to first page
      params.set("page", "1");
      return params;
    });
  };

  // Keep local pagination state in sync with URL so the header
  // gets the right current page and per-page values.
  useEffect(() => {
    const perPageParam = searchParams.get("per_page");
    const pageParam = searchParams.get("page");

    const perPageValue = (() => {
      const parsed = perPageParam ? Number(perPageParam) : NaN;
      return Number.isFinite(parsed) && parsed > 0 ? parsed : 20;
    })();

    const pageValue = (() => {
      const parsed = pageParam ? Number(pageParam) : NaN;
      return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
    })();

    setPerPage(perPageValue);
    setCurrentPage(pageValue);
  }, [searchParams]);

  const handlePageChange = (page: number) => {
    setSearchParams((prev) => {
      const params = new URLSearchParams(prev);
      params.set("page", page.toString());
      return params;
    });
  };

  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;

  return (
    <>
      <GalleryHeader
        onPerPageChange={handlePerPageChange}
        initialPerPage={perPage}
        pagination={pagination}
        currentPage={currentPage}
        onPageChange={handlePageChange}
      />
      {loading ? (
        <div className="p-4">Loading photos...</div>
      ) : (
        <div className="photo-grid">
          {photos.map((photo) => (
            <PhotoThumbnail key={photo.id} photo={photo} />
          ))}
        </div>
      )}
    </>
  );
}
