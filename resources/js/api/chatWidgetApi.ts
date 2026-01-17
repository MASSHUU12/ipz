import { instance } from "./api";

export interface ChatWidgetRequestPayload {
  content: string;
  timezone?: string;
  sessionId?: string;
}

export interface ChatWidgetResponsePayload {
  question: string;
  answer: string;
}

export interface ChatbotSuggestion {
  suggestion: string | null;
  description: string | null;
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

export const fetchChatbotSuggestions = async (
  limit = 12,
): Promise<ChatbotSuggestion[]> => {
  const { data } = await instance.get<ChatbotSuggestion[]>("/chatbot/suggest", {
    params: { limit },
  });

  return data ?? [];
};
