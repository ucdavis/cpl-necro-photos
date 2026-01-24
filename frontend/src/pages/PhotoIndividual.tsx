import { useParams, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { getPhotoById } from "../services/photoService";
import type { Photo } from "../types";
import { formatDateTime } from "../utils/date";

export default function PhotoIndividual() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [photo, setPhoto] = useState<Photo | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchPhoto() {
      if (!id) return;

      try {
        setLoading(true);
        const photoData = await getPhotoById(id);
        setPhoto(photoData);
      } catch (err) {
        const errorMessage =
          err instanceof Error ? err.message : "Failed to load photo";
        setError(errorMessage);
      } finally {
        setLoading(false);
      }
    }

    fetchPhoto();
  }, [id]);

  if (loading) return <div className="p-4">Loading photo...</div>;
  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;
  if (!photo) return <div className="p-4">Photo not found</div>;

  const photoUrl = `${import.meta.env.VITE_PHOTO_URL}/${photo.year}/${photo.filename}`;

  return (
    <div className="photo-individual-container">
      <div className="photo-individual-grid">
        <div className="photo-individual-wrapper">
          {/* Individual photo */}
          <img
            src={photoUrl}
            alt={`Photo ${photo.cpl_num}`}
            className="photo-individual-image"
          />
        </div>
        <div className="photo-info-panel">
          <button onClick={() => navigate(-1)} className="back-button">
            Back to Gallery
          </button>
          <h3 className="photo-title">
            {photo.year}
            {photo.suffix}-{photo.cpl_num}
          </h3>
          {/* Photo details */}
          <div className="photo-details">
            <p className="photo-uploader">
              <strong>Uploaded by:</strong> {photo.login}
            </p>
            <p className="photo-date">
              <strong>Uploaded:</strong> {formatDateTime(photo.date_uploaded)}
            </p>
            <p className="photo-size">
              <strong>File name:</strong> {photo.filename}
            </p>
            <p className="photo-size">
              <strong>File size:</strong> {(photo.size / 1024).toFixed(1)} KB
            </p>
            <p className="photo-link">
              <a
                href={photoUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="original-image-link"
              >
                Open original image
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
