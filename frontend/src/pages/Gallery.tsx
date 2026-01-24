import { useState, useEffect } from "react";
import { useSearchParams } from "react-router-dom";
import { getPhotos } from "../services/photoService";
import { PhotoThumbnail } from "../components/PhotoThumbnail";
import type { Photo } from "../types";

export default function Gallery() {
  const [photos, setPhotos] = useState<Photo[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchParams] = useSearchParams();

  useEffect(() => {
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

        const result = await getPhotos(queryParams.toString());

        setPhotos(result?.data || []);
      } catch (err) {
        const errorMessage =
          err instanceof Error ? err.message : "An error occurred";
        setError(errorMessage);
      } finally {
        setLoading(false);
      }
    }

    fetchPhotos();
  }, [searchParams]); // Re-fetch when URL params change

  if (loading) return <div className="p-4">Loading photos...</div>;
  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;

  return (
    <div className="photo-grid">
      {photos.map((photo) => (
        <PhotoThumbnail key={photo.id} photo={photo} />
      ))}
    </div>
  );
}
