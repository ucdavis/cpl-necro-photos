import { useState, useEffect } from "react";
import { getPhotos } from "../services/photoService";
import { PhotoThumbnail } from "../components/PhotoThumbnail";
import type { Photo } from "../types";

export default function Gallery() {
  const [photos, setPhotos] = useState<Photo[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchPhotos();
  }, []);

  async function fetchPhotos() {
    try {
      setLoading(true);
      const result = await getPhotos();
      console.log(result);
      setPhotos(result?.data || []);
    } catch (err) {
      const errorMessage =
        err instanceof Error ? err.message : "An error occurred";
      setError(errorMessage);
    } finally {
      setLoading(false);
    }
  }

  if (loading) return <div className="p-4">Loading photos...</div>;
  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;

  return (
    <div className="p-4">
      <h2>Gallery Page</h2>

      <div className="photo-grid">
        {photos.map((photo) => (
          <PhotoThumbnail
            key={photo.id}
            year={photo.year}
            filename={photo.filename}
          />
        ))}
      </div>
    </div>
  );
}
