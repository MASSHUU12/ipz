// resources/js/api/emailApi.ts
import { instance, Payload } from "./api";

export interface UpdateEmailPayload {
    email: string;
}

export interface UpdateEmailResponse {
    message: string;
}

export const updateEmail = async (
    payload: Payload & UpdateEmailPayload
): Promise<UpdateEmailResponse | null> => {
    const { token, email } = payload;
    if (!token) {
        console.error("Token is empty.");
        return null;
    }
    try {
        const res = await instance.post<UpdateEmailResponse>(
            "/user/email",
            { email },
            { headers: { Authorization: `Bearer ${token}` } }
        );
        return res.data;
    } catch (e: unknown) {
        console.error(e);
        return null;
    }
};

export interface ResendVerificationResponse {
    message: string;
}

export const resendVerificationEmail = async (
    payload: Payload
): Promise<ResendVerificationResponse | null> => {
    const { token } = payload;
    if (!token) {
        console.error("Token is empty.");
        return null;
    }
    try {
        const res = await instance.post<ResendVerificationResponse>(
            "/email/verification-notification",
            {},
            { headers: { Authorization: `Bearer ${token}` } }
        );
        return res.data;
    } catch (e: unknown) {
        console.error(e);
        return null;
    }
};
