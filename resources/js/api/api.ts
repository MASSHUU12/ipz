import axios from "axios";

export const instance = axios.create({
  baseURL: "/api",
  timeout: 1000,
  headers: {
    Accept: "application/json",
  },
});
