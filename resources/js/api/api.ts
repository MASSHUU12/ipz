import axios from "axios";

export interface Payload {
  token?: string;
}

export const instance = axios.create({
  baseURL: "/api",
  timeout: 1000,
  headers: {
    Accept: "application/json",
  },
});
