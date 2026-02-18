import { Link } from "react-router-dom";
import type { Photo } from "../types";
import { formatDate } from "../utils/date";

// Helper function to detect video files
function isVideoFile(filename: string): boolean {
  const videoExtensions = [".mov", ".mp4", ".avi", ".mkv", ".webm"];
  return videoExtensions.some((ext) => filename.toLowerCase().endsWith(ext));
}

export function PhotoThumbnail({ photo }: { photo: Photo }) {
  const isVideo = isVideoFile(photo.filename);
  const uploadsBase = import.meta.env.VITE_PHOTO_URL?.replace(/\/$/, "") ?? "";
  const mediaUrl = isVideo
    ? `${uploadsBase}/${photo.year}/${photo.filename}`
    : `${uploadsBase}/${photo.year}/thumbnails/${photo.filename}`;

  return (
    <Link
      to={`/photo/${photo.id}`}
      className="no-underline hover:no-underline focus:outline-none"
    >
      <div className="photo-thumbnail">
        <div className="relative">
          {isVideo ? (
            <>
              <video
                src={mediaUrl}
                className="w-full h-full object-cover"
                style={{ aspectRatio: "350/262" }}
                muted
                preload="metadata"
                crossOrigin="anonymous"
              />
              <div className="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 hover:bg-opacity-20 transition-all">
                <div className="bg-white bg-opacity-90 rounded-full p-3 shadow-lg">
                  <svg
                    className="w-6 h-6 text-gray-800"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path d="M8 5v14l11-7z" />
                  </svg>
                </div>
              </div>
            </>
          ) : (
            <img src={mediaUrl} alt={`Thumbnail of photo ${photo.cpl_num}`} />
          )}
        </div>
        <div className="thumbnail-caption">
          <div className="caption-top flex justify-between items-center">
            <div className="left-info flex flex-col">
              <span className="caption-filename font-medium text-sm text-gray-700">
                {photo.year}
                {photo.suffix}-{photo.cpl_num}
              </span>
              <span className="caption-date text-xs text-gray-500 mt-1">
                {formatDate(photo.date_uploaded)}
              </span>
            </div>

            <div className="right-info flex flex-col items-end text-sm text-gray-600">
              <span className="caption-login">{photo.login}</span>
              <span className="caption-filename-muted break-words text-gray-500">
                {photo.filename}
              </span>
            </div>
          </div>
        </div>
      </div>
    </Link>
  );
}
