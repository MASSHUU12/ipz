import { instance } from "./api";

export interface ChatWidgetRequestPayload {
  content: string;
  timezone?: string;
  sessionId?: string;
}

export interface MapPayload {
  type: "map";
  lat: number;
  lng: number;
  label?: string;
  zoom?: number;
}

export interface ChatWidgetResponsePayload {
  question: string;
  answer: string;
  payload?: MapPayload | null;
}

export const sendChatWidgetMessage = async (
  payload: ChatWidgetRequestPayload,
): Promise<ChatWidgetResponsePayload> => {
  const { data } = await instance.post<ChatWidgetResponsePayload>("/chatbot/message", {
    content: payload.content,
    timezone: payload.timezone,
    session_id: payload.sessionId,
  });

  return data;
};
