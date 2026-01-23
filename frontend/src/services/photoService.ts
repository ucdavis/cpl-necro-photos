import { apiClient } from "./apiClient";
import type { PhotosResponse, Photo, UploadResponse } from "../types";

export const getPhotos = async (queryParams: string = ""): Promise<PhotosResponse> => {
  const url = queryParams ? `/photos?${queryParams}` : "/photos";
  const res = await apiClient.get<PhotosResponse>(url);
  return res.data;
};

export const getPhotoById = async (id: string): Promise<Photo> => {
  const res = await apiClient.get<Photo>(`/photos/${id}`);
  return res.data;
};

export const uploadPhoto = async (
  photoData: FormData,
): Promise<UploadResponse> => {
  const res = await apiClient.post<UploadResponse>("/photos/upload", photoData);
  return res.data;
};
