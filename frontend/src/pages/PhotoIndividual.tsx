import { useParams, useNavigate } from "react-router-dom";
import axios from "axios";
import { useState, useEffect } from "react";
import { getPhotoById, reassignPhotoAccession } from "../services/photoService";
import type { Photo } from "../types";
import { formatDateTime } from "../utils/date";
import ReassignModal from "../components/ReassignModal";

// Helper function to detect video files
function isVideoFile(filename: string): boolean {
  const videoExtensions = [".mov", ".mp4", ".avi", ".mkv", ".webm"];
  return videoExtensions.some((ext) => filename.toLowerCase().endsWith(ext));
}

export default function PhotoIndividual() {
  // Get photo ID from URL params
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  // State
  const [photo, setPhoto] = useState<Photo | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showReassignModal, setShowReassignModal] = useState(false);
  const [reassignError, setReassignError] = useState<string | null>(null);
  const [reassignLoading, setReassignLoading] = useState(false);

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

  const handleOpenReassignModal = () => {
    setReassignError(null);
    setShowReassignModal(true);
  };

  const handleCloseReassignModal = () => {
    if (reassignLoading) return;
    setShowReassignModal(false);
  };

  const handleConfirmReassign = async (data: {
    accession: string;
    year: string;
  }) => {
    const { accession, year } = data;

    if (!accession) {
      setReassignError("Please enter a new accession number.");
      return;
    }

    if (!year) {
      setReassignError("Please select a new accession year.");
      return;
    }

    try {
      setReassignLoading(true);
      setReassignError(null);

      // Call service method to reassign photo accession
      await reassignPhotoAccession({
        photoId: photo.id,
        newAccession: accession.trim(),
        newYear: year.trim(),
      });

      setShowReassignModal(false);
    } catch (err) {
      let message = "Failed to reassign photo";

      if (axios.isAxiosError(err)) {
        message = err.response?.data?.error ?? err.message ?? message;
      } else if (err instanceof Error) {
        message = err.message;
      }

      setReassignError(message);
    } finally {
      setReassignLoading(false);
    }
  };

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
            <p>
              <button
                type="button"
                onClick={handleOpenReassignModal}
                className="green-btn"
              >
                Reassign image to different accession
              </button>
            </p>
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
                className="green-btn"
              >
                Open original {isVideo ? "video" : "image"}
              </a>
            </p>
          </div>
        </div>
      </div>

      {showReassignModal && (
        <ReassignModal
          photo={photo}
          onConfirm={handleConfirmReassign}
          onCancel={handleCloseReassignModal}
          error={reassignError}
          loading={reassignLoading}
        />
      )}
    </div>
  );
}
