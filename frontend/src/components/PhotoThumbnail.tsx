interface PhotoThumbnailProps {
  filename: string;
  year: number;
}

export function PhotoThumbnail({ filename, year }: PhotoThumbnailProps) {
  const photoUrl = `${import.meta.env.VITE_PHOTO_URL}/${year}/${filename}`;

  return (
    <div className="photo-thumbnail">
      <img src={photoUrl} alt="Photo thumbnail" />
    </div>
  );
}
