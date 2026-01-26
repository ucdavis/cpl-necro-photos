import axios from "axios";

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  // Remove default Content-Type to allow automatic setting for FormData
  headers: {},
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => Promise.reject(error),
);
