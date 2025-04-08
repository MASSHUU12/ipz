import { instance, Payload } from "./api";

export interface Role {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
  pivot: {
    model_type: string;
    model_id: number;
    role_id: number;
  };
}

export interface User {
  id: number;
  email: string | null;
  phone_number: string | null;
  email_verified_at: string | null;
  phone_number_verified_at: string | null;
  failed_login_attempts: number;
  created_at: string;
  updated_at: string;
  blocked_until: string | null;
  roles: Role[];
}

export interface GetCurrentUserResponse {
  user: User;
}

export const getCurrentUser = async (
  payload: Payload,
): Promise<GetCurrentUserResponse | null> => {
  if (!payload.token && payload.token!.length <= 0) {
    console.error("Token is empty.");
    return null;
  }

  try {
    const response = await instance.get<GetCurrentUserResponse>("/user", {
      headers: {
        Authorization: `Bearer ${payload.token}`,
      },
    });
    return response.data;
  } catch (error: unknown) {
    if (error instanceof Error) {
      console.error(error);
    } else {
      console.error("An unknown error occurred.");
    }
    return null;
  }
};
