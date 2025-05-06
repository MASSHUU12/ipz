import React from "react";
import { router } from "@inertiajs/react";

export const RequireAuth: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const token = localStorage.getItem("authToken");

  if (!token) {
    router.visit("/login");
    return null;
  }
  return <>{children}</>;
};
