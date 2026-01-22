import { apiClient } from "./ApiClient";

export const getPhotos = async () => {
  const result = await apiClient.get("/photos");
  return result;
};

export const getPhotoById = async (id: string) => {
  const result = await apiClient.get(`/photos/${id}`);
  return result;
};

export const uploadPhoto = async (photoData: FormData) => {
  const result = await apiClient.post("/photos", photoData, {
    headers: { "Content-Type": "multipart/form-data" },
  });
  return result;
};
