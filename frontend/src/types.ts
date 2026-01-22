export interface Photo {
  id: number;
  cpl_num: string;
  suffix?: string;
  year: number;
  filename: string;
  size: number;
  date_uploaded: string;
  login: string;
}

export interface PaginationInfo {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
  from: number;
  to: number;
}

export interface PhotosResponse {
  data: Photo[];
  pagination: PaginationInfo;
}

export interface ApiError {
  error: string;
}

export interface UploadResponse {
  success: boolean;
  message: string;
  data: {
    id: number;
    filename: string;
  };
}
