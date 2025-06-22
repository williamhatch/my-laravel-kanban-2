import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8000",
  withCredentials: true, // Important for Sanctum
  // withXSRFToken: true,
});

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default apiClient;
