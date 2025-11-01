export interface ChatWidgetRequestPayload {
  content: string;
  timezone?: string;
  sessionId?: string;
}

export interface ChatWidgetResponsePayload {
  answer: string;
}

interface SimulatedRule {
  test: RegExp;
  responses?: string[];
  callback?: (matches: RegExpMatchArray) => string;
}

const delay = (ms: number) =>
  new Promise(resolve => {
    const timer = typeof window !== "undefined" ? window.setTimeout : setTimeout;
    timer(resolve, ms);
  });

const SIMULATED_RULES: SimulatedRule[] = [
  {
    test: /^\s*$/,
    responses: [
      "Nie widzę wiadomości. Czy możesz napisać swoje pytanie?",
      "Wygląda na to, że wiadomość była pusta. Napisz proszę, jak mogę pomóc.",
    ],
  },
  {
    test: /\bczas\b|\bgodzina\b/i,
    callback: () => {
      const now = new Date();
      return `Symulowana odpowiedź: obecna godzina to ${now.toLocaleTimeString()}.`;
    },
  },
  {
    test: /\bpalindrom\b/i,
    callback: matches => {
      const phrase = matches.input?.replace(/palindrom/gi, "").trim() ?? "";
      const normalized = phrase.toLowerCase().replace(/[\W_]+/g, "");
      const reversed = normalized.split("").reverse().join("");
      const isPalindrome = normalized.length > 0 && normalized === reversed;
      if (!phrase) {
        return "Podaj tekst, a sprawdzę, czy to palindrom.";
      }
      return `'${phrase}' ${isPalindrome ? "jest" : "nie jest"} palindromem.`;
    },
  },
  {
    test: /\b(witaj|cześć|hello)\b/i,
    responses: [
      "Cześć! Jestem symulowaną wersją chatbota. W przyszłości będę korzystał z danych z backendu.",
      "Hej! Tutaj frontendowy chatbot. Gdy backend będzie gotowy, odpowiem jeszcze lepiej.",
    ],
  },
];

const simulateAssistantMessage = async (content: string): Promise<ChatWidgetResponsePayload> => {
  await delay(450 + Math.random() * 400);

  for (const rule of SIMULATED_RULES) {
    const match = content.match(rule.test);
    if (!match) {
      continue;
    }

    if (rule.callback) {
      try {
        const value = rule.callback(match);
        if (value) {
          return { answer: value };
        }
      } catch (error) {
        console.error("Symulowany callback nie powiódł się", error);
      }
    }

    if (rule.responses?.length) {
      return {
        answer: rule.responses[Math.floor(Math.random() * rule.responses.length)],
      };
    }
  }

  return {
    answer:
      "To przykładowa odpowiedź z frontendu. Po podłączeniu backendu pojawią się tu realne wyniki.",
  };
};

export const sendChatWidgetMessage = async (
  payload: ChatWidgetRequestPayload,
): Promise<ChatWidgetResponsePayload> => {
  // TODO: Gdy endpoint /chatbot/message będzie dostępny, wyślij żądanie przez instance.post zamiast symulacji.
  // Example:
  // import { instance } from "./api";
  // const { data } = await instance.post<ChatWidgetResponsePayload>("/chatbot/message", {
  //   content: payload.content,
  //   timezone: payload.timezone,
  //   session_id: payload.sessionId,
  // });
  // return data;

  return simulateAssistantMessage(payload.content);
};
