import { Link } from "react-router-dom";
import type { Photo } from "../types";
import { formatDate } from "../utils/date";

export function PhotoThumbnail({ photo }: { photo: Photo }) {
  const photoUrl = `${import.meta.env.VITE_PHOTO_URL}/${photo.year}/thumbnails/${photo.filename}`;

  return (
    <Link to={`/photo/${photo.id}`}>
      <div className="photo-thumbnail">
        <img src={photoUrl} alt={`Thumbnail of photo ${photo.cpl_num}`} />
        <div className="thumbnail-caption">
          <div className="caption-top">
            <span className="caption-filename">
              {photo.year}
              {photo.suffix}-{photo.cpl_num}
            </span>
            <span className="caption-login">{photo.login}</span>
          </div>
          <div className="caption-date">{formatDate(photo.date_uploaded)}</div>
        </div>
      </div>
    </Link>
  );
}
