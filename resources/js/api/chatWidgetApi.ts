import { instance } from "./api";

export interface ChatWidgetPayload {
    message: string;
}

export interface ChatWidgetResponse {
    response?: string;
}

export const sendChatWidgetMessage = async (
    payload: ChatWidgetPayload
): Promise<ChatWidgetResponse | null> => {
    try {
        const res = await instance.post<ChatWidgetResponse>("/chat", payload);
        return res.data;
    } catch (error: unknown) {
        console.error(error);
        return null;
    }
};
