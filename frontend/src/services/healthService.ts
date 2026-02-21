import { apiClient } from "./apiClient";

export interface HealthResponse {
  success: boolean;
  timestamp?: string;
}

export async function checkHealth(): Promise<HealthResponse> {
  const response = await apiClient.get<HealthResponse>("/health", {
    // Avoid caching so we always hit the server
    headers: {
      "Cache-Control": "no-cache",
    },
  });
  return response.data;
}
