import { useParams, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { getPhotoById } from "../services/photoService";
import type { Photo } from "../types";
import { formatDateTime } from "../utils/date";

// Helper function to detect video files
function isVideoFile(filename: string): boolean {
  const videoExtensions = [".mov", ".mp4", ".avi", ".mkv", ".webm"];
  return videoExtensions.some((ext) => filename.toLowerCase().endsWith(ext));
}

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

  if (loading) return <div className="p-4">Loading...</div>;
  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;
  if (!photo) return <div className="p-4">Media not found</div>;

  const isVideo = isVideoFile(photo.filename);
  const uploadsBase = import.meta.env.VITE_PHOTO_URL?.replace(/\/$/, "") ?? "";
  const mediaUrl = `${uploadsBase}/${photo.year}/${photo.filename}`;
  return (
    <div className="photo-individual-container">
      <div className="photo-individual-grid">
        <div className="photo-individual-wrapper">
          {isVideo ? (
            <video
              src={mediaUrl}
              controls
              className="photo-individual-image"
              style={{ maxWidth: "100%", height: "auto" }}
              preload="metadata"
              crossOrigin="anonymous"
            >
              Your browser does not support the video tag.
            </video>
          ) : (
            <img
              src={mediaUrl}
              alt={`Photo ${photo.cpl_num}`}
              className="photo-individual-image"
            />
          )}
        </div>
        <div className="photo-info-panel">
          <button onClick={() => navigate(-1)} className="back-button">
            Back to Gallery
          </button>
          <h3 className="photo-title">
            {photo.year}
            {photo.suffix}-{photo.cpl_num}
          </h3>
          {/* Media details */}
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
                href={mediaUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="original-image-link"
              >
                Open original {isVideo ? "video" : "image"}
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
