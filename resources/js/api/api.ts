import axios from "axios";

export interface Payload {
  token?: string;
}

export const instance = axios.create({
  baseURL: "/api",
  timeout: 10000,
  headers: {
    Accept: "application/json",
  },
});
