import { Link } from "react-router-dom";
import type { Photo } from "../types";
import { formatDate } from "../utils/date";

export function PhotoThumbnail({ photo }: { photo: Photo }) {
  const photoUrl = `${import.meta.env.VITE_PHOTO_URL}/${photo.year}/thumbnails/${photo.filename}`;

  return (
    <Link
      to={`/photo/${photo.id}`}
      className="no-underline hover:no-underline focus:outline-none"
    >
      <div className="photo-thumbnail">
        <img src={photoUrl} alt={`Thumbnail of photo ${photo.cpl_num}`} />
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
