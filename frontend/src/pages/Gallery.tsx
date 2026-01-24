import { useState, useEffect } from "react";
import { useSearchParams } from "react-router-dom";
import { getPhotos } from "../services/photoService";
import { PhotoThumbnail } from "../components/PhotoThumbnail";
import { GalleryHeader } from "../components/GalleryHeader";
import type { Photo, PaginationInfo } from "../types";

export default function Gallery() {
  const [photos, setPhotos] = useState<Photo[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchParams] = useSearchParams();
  const [perPage, setPerPage] = useState(20);
  const [currentPage, setCurrentPage] = useState(1);
  const [pagination, setPagination] = useState<PaginationInfo | null>(null);

  useEffect(() => {
    // Fetch photos whenever search params, perPage, or currentPage change
    async function fetchPhotos() {
      try {
        setLoading(true);

        // Get search and year from URL params
        const search = searchParams.get("search");
        const year = searchParams.get("year");

        // If searching, don't filter by year (search all years)
        // If not searching, use year filter if provided
        const queryParams = new URLSearchParams();
        if (search) {
          queryParams.set("search", search);
        } else if (year) {
          queryParams.set("year", year);
        }
        queryParams.set("per_page", perPage.toString());
        queryParams.set("page", currentPage.toString());

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
  }, [searchParams, perPage, currentPage]); // Re-fetch when URL params, perPage, or page change

  // Reset to page 1 when search params change (filter changes)
  useEffect(() => {
    setCurrentPage(1);
  }, [searchParams]);

  const handlePerPageChange = (newPerPage: number) => {
    setPerPage(newPerPage);
    setCurrentPage(1); // Reset to first page when changing per-page
  };

  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;

  return (
    <>
      <GalleryHeader
        onPerPageChange={handlePerPageChange}
        initialPerPage={perPage}
        pagination={pagination}
        currentPage={currentPage}
        onPageChange={setCurrentPage}
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
