import { FocusEvent, FormEvent, MouseEvent, useEffect, useMemo, useRef, useState } from "react";
import { createPortal } from "react-dom";
import { isAxiosError } from "axios";
import {
  ChatbotSuggestion,
  fetchChatbotSuggestions
} from "../../api/chatWidgetApi";
import { useDebounce } from "../../hooks/useDebounce";
import { sendChatWidgetMessage, MapPayload } from "../../api/chatWidgetApi";
import { ChatIcon } from "./ChatIcon";
import { SuggestionBubble } from "./SuggestionBubble";
import { MapDisplay } from "./MapDisplay";
import "./ChatWidget.css";

type MessageRole = "user" | "assistant";
type MessageFeedback = "up" | "down";

interface MessageRating {
  value: MessageFeedback;
  expiresAt: number;
  locked: boolean;
}

interface ChatMessage {
  id: string;
  role: MessageRole;
  content: string;
  payload?: MapPayload | null;
}

const hiddenClass = "chat-widget--hidden";
const ratingWindowMs = 5_000;
const suggestionLimit = 4;

const suggestionPresets = [
  "Jaka jest prognoza pogody na jutro?",
  "Jakie są aktualne warunki jakości powietrza?",
  "Czy powinienem wziąć parasol dzisiaj?",
  "Jak wygląda sytuacja drogowa w mojej okolicy?",
  "Podpowiedz, jak przygotować się na wichurę?",
  "Czy możesz streścić najważniejsze alerty pogodowe?",
  "Jak mogę poprawić komfort oddychania w domu?",
  "Gdzie znajdę najbliższe schronienie w razie burzy?",
];

const buildSuggestions = (query: string, backend: ChatbotSuggestion[]): string[] => {
  const normalized = query.trim().toLowerCase();
  const backendPool = backend
    .map(item => item?.suggestion?.trim())
    .filter((entry): entry is string => Boolean(entry));

  const matchingBackend = normalized
    ? backendPool.filter(entry => entry.toLowerCase().includes(normalized))
    : backendPool;

  const fallbackPool = normalized
    ? suggestionPresets.filter(entry => entry.toLowerCase().includes(normalized))
    : suggestionPresets;

  const dynamicPool = normalized
    ? [
      `Opowiedz mi więcej o ${query}.`,
      `Jakie są prognozy dotyczące ${query}?`,
      `Czy są ostrzeżenia związane z ${query}?`,
    ]
    : [];

  const unique = Array.from(new Set([...matchingBackend, ...fallbackPool, ...dynamicPool]));
  return unique.slice(0, suggestionLimit);
};

const buildId = () => Math.random().toString(36).slice(2);

export const ChatWidget = () => {
  const [mounted, setMounted] = useState(false);
  const [isOpen, setIsOpen] = useState(false);
  const [isSending, setIsSending] = useState(false);
  const [inputValue, setInputValue] = useState("");
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [ratings, setRatings] = useState<Record<string, MessageRating>>({});
  const [backendSuggestions, setBackendSuggestions] = useState<ChatbotSuggestion[]>([]);
  const [suggestions, setSuggestions] = useState<string[]>([]);
  const [isInputFocused, setIsInputFocused] = useState(false);

  const messagesEndRef = useRef<HTMLDivElement | null>(null);
  const initialFocusRef = useRef<HTMLTextAreaElement | null>(null);
  const ratingTimers = useRef<Record<string, number>>({});
  const blurTimeoutRef = useRef<number | null>(null);
  const suggestionsRef = useRef<HTMLDivElement | null>(null);
  const resolvedTimezone = useMemo(() => {
    try {
      return Intl.DateTimeFormat().resolvedOptions().timeZone;
    } catch (err) {
      console.warn("Nie udało się określić strefy czasowej użytkownika.", err);
      return undefined;
    }
  }, []);

  const [now, setNow] = useState(() => Date.now());
  const debouncedInputValue = useDebounce(inputValue, 300);

  useEffect(() => {
    setMounted(true);
    return () => {
      Object.values(ratingTimers.current).forEach(timeoutId => {
        window.clearTimeout(timeoutId);
      });
      if (blurTimeoutRef.current) {
        window.clearTimeout(blurTimeoutRef.current);
      }
      setMounted(false);
    };
  }, []);

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    const timeout = window.setTimeout(() => {
      messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
      initialFocusRef.current?.focus();
    }, 60);

    return () => {
      window.clearTimeout(timeout);
    };
  }, [isOpen, messages]);

  useEffect(() => {
    let cancelled = false;
    const loadBackendSuggestions = async () => {
      try {
        const data = await fetchChatbotSuggestions(12);
        if (!cancelled) {
          setBackendSuggestions(data);
        }
      } catch (error) {
        if (!cancelled) {
          console.error("Nie udało się pobrać sugestii czatu", error);
        }
      }
    };

    loadBackendSuggestions();

    return () => {
      cancelled = true;
    };
  }, [debouncedInputValue]);

  useEffect(() => {
    setSuggestions(buildSuggestions(inputValue, backendSuggestions));
  }, [inputValue, backendSuggestions]);

  useEffect(() => {
    const intervalId = window.setInterval(() => {
      setNow(Date.now());
    }, 200);

    return () => {
      window.clearInterval(intervalId);
    };
  }, []);

  const toggleWidget = () => {
    setIsOpen(prev => !prev);
    setErrorMessage(null);
  };

  const clearHistory = () => {
    const confirmed = window.confirm("Czy na pewno chcesz usunąć historię czatu?");
    if (!confirmed) return;

    Object.values(ratingTimers.current).forEach(timeoutId => {
      window.clearTimeout(timeoutId);
    });
    ratingTimers.current = {};
    setMessages([]);
    setRatings({});
    setErrorMessage(null);
    setInputValue("");
    setIsSending(false);
  };

  const handleClearMouseDown = (event: MouseEvent<HTMLButtonElement>) => {
    event.preventDefault();
  };

  const canSubmit = useMemo(() => {
    return inputValue.trim().length > 0 && !isSending;
  }, [inputValue, isSending]);

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!canSubmit) {
      return;
    }

    const trimmed = inputValue.trim();
    const userMessage: ChatMessage = {
      id: buildId(),
      role: "user",
      content: trimmed,
    };

    setMessages(prev => [...prev, userMessage]);
    setInputValue("");
    setErrorMessage(null);
    setIsSending(true);

    try {
      const { answer: assistantText, payload } = await sendChatWidgetMessage({
        content: trimmed,
        timezone: resolvedTimezone,
      });

      const isTechnicalError =
        typeof assistantText === "string" &&
        /sqlstate|base table|doesn't exist|table or view not found/i.test(assistantText);

      if (isTechnicalError) {
        setErrorMessage("Wystąpił problem po stronie serwera. Spróbuj ponownie za chwilę.");
        setMessages(prev => [
          ...prev,
          {
            id: buildId(),
            role: "assistant",
            content: "Przepraszamy, nie mogliśmy przetworzyć odpowiedzi. Spróbuj ponownie później.",
          },
        ]);
        return;
      }

      const assistantMessage: ChatMessage = {
        id: buildId(),
        role: "assistant",
        content: assistantText,
        payload: payload ?? null,
      };

      setMessages(prev => [...prev, assistantMessage]);
    } catch (error) {
      let fallbackMessage = "Wystąpił nieoczekiwany błąd.";
      if (isAxiosError(error)) {
        const validationMessage =
          (error.response?.data as { message?: string })?.message ?? error.message;
        fallbackMessage = validationMessage || fallbackMessage;
      } else if (error instanceof Error) {
        fallbackMessage = error.message || fallbackMessage;
      }

      setErrorMessage(fallbackMessage);
      setMessages(prev => [
        ...prev,
        {
          id: buildId(),
          role: "assistant",
          content: "Przepraszamy, nie udało się uzyskać odpowiedzi. Spróbuj ponownie.",
        },
      ]);
    } finally {
      setIsSending(false);
    }
  };

  const lockRating = (messageId: string) => {
    setRatings(prev => {
      const current = prev[messageId];
      if (!current || current.locked) {
        return prev;
      }
      return {
        ...prev,
        [messageId]: {
          ...current,
          locked: true,
        },
      };
    });

    const timerId = ratingTimers.current[messageId];
    if (timerId) {
      window.clearTimeout(timerId);
      delete ratingTimers.current[messageId];
    }
  };

  const handleRateMessage = (messageId: string, feedback: MessageFeedback) => {
    setRatings(prev => {
      const current = prev[messageId];

      if (current?.locked) {
        return prev;
      }

      const expiresAt = Date.now() + ratingWindowMs;

      const next: Record<string, MessageRating> = {
        ...prev,
        [messageId]: {
          value: feedback,
          expiresAt,
          locked: current?.locked ?? false,
        },
      };

      if (ratingTimers.current[messageId]) {
        window.clearTimeout(ratingTimers.current[messageId]);
      }
      ratingTimers.current[messageId] = window.setTimeout(() => {
        lockRating(messageId);
      }, ratingWindowMs);
      return next;
    });
  };

  const handleSuggestionClick = (suggestion: string) => {
    setInputValue(suggestion);
    setIsInputFocused(true);
    initialFocusRef.current?.focus();
  };

  const handleSuggestionMouseDown = (event: MouseEvent<HTMLButtonElement>) => {
    event.preventDefault();
    handleInputFocus();
  };

  const handleCloseMouseDown = (event: MouseEvent<HTMLButtonElement>) => {
    event.preventDefault();
  };

  const handleInputFocus = () => {
    if (blurTimeoutRef.current) {
      window.clearTimeout(blurTimeoutRef.current);
      blurTimeoutRef.current = null;
    }
    setIsInputFocused(true);
  };

  const handleInputBlur = (event: FocusEvent<HTMLTextAreaElement>) => {
    const nextFocus = event.relatedTarget;
    if (nextFocus && suggestionsRef.current?.contains(nextFocus as Node)) {
      return;
    }
    if (blurTimeoutRef.current) {
      window.clearTimeout(blurTimeoutRef.current);
    }
    blurTimeoutRef.current = window.setTimeout(() => {
      setIsInputFocused(false);
      blurTimeoutRef.current = null;
    }, 120);
  };

  const hasActiveCountdown = useMemo(() => {
    return Object.values(ratings).some(rating => !rating.locked && rating.expiresAt > now);
  }, [ratings, now]);

  const shouldShowSuggestions =
    (isInputFocused || inputValue.trim().length > 0 || hasActiveCountdown) &&
    suggestions.length > 0;

  if (!mounted || typeof document === "undefined") {
    return null;
  }

  return createPortal(
    <div className="chat-widget">
      <button
        type="button"
        className={`chat-widget__toggle ${isOpen ? hiddenClass : ""}`}
        aria-label="Otwórz czat"
        onClick={toggleWidget}
      >
        <ChatIcon className="chat-widget__icon" />
      </button>

      <div className={`chat-widget__panel ${isOpen ? "is-open" : ""}`}>
        <header className="chat-widget__header">
          <div className="chat-widget__title">
            <ChatIcon className="chat-widget__title-icon" />
            <span>Wsparcie AI</span>
          </div>
          <div className="chat-widget__actions">
            <button
              type="button"
              className="chat-widget__clear"
              onMouseDown={handleClearMouseDown}
              onClick={clearHistory}
              aria-label="Wyczyść historię czatu"
            >
              Wyczyść historię
            </button>
            <button
              type="button"
              className="chat-widget__close"
              aria-label="Zamknij czat"
              onMouseDown={handleCloseMouseDown}
              onClick={toggleWidget}
            >
              ×
            </button>
          </div>
        </header>

        <div className="chat-widget__body">
          <div className="chat-widget__messages">
            {messages.length === 0 && (
              <div className="chat-widget__empty">
                <p>Zadaj pytanie, a spróbujemy pomóc.</p>
              </div>
            )}

            {messages.map(message => {
              const rating = ratings[message.id];
              const isRatingLocked = Boolean(rating?.locked);
              const selectedFeedback = rating?.value;
              const lockedLabel =
                selectedFeedback === "down" ? "Oceniono negatywnie" : "Oceniono pozytywnie";
              const remainingMs = rating ? Math.max(rating.expiresAt - now, 0) : 0;
              const isCountdownActive = Boolean(rating && !isRatingLocked);
              const countdownPercent = Math.min(100, Math.max(0, (remainingMs / ratingWindowMs) * 100));
              const countdownSeconds = Math.max(0, Math.ceil(remainingMs / 1000));

              return (
                <div
                  key={message.id}
                  className={`chat-widget__message chat-widget__message--${message.role}`}
                >
                  <span className={`chat-widget__bubble chat-widget__bubble--${message.role}`}>
                    {message.content}
                  </span>
                  {message.payload && message.payload.type === "map" && (
                    <MapDisplay payload={message.payload} />
                  )}
                  {isCountdownActive && (
                    <div
                      className="chat-widget__rating-progress"
                      role="progressbar"
                      aria-valuemin={0}
                      aria-valuemax={ratingWindowMs / 1000}
                      aria-valuenow={countdownSeconds}
                      aria-label={`Pozostało ${countdownSeconds} s na zmianę oceny`}
                    >
                      <div
                        className="chat-widget__rating-progress-bar"
                        style={{ width: `${countdownPercent}%` }}
                      />
                    </div>
                  )}
                  {message.role === "assistant" && (
                    <div
                      className={`chat-widget__feedback${isRatingLocked ? " chat-widget__feedback--locked" : ""
                        }`}
                      aria-label="Oceń odpowiedź"
                    >
                      {isRatingLocked ? (
                        <button
                          type="button"
                          className="chat-widget__feedback-button is-selected is-locked"
                          disabled
                          aria-label={lockedLabel}
                        >
                          {selectedFeedback === "up" ? "👍" : "👎"}
                        </button>
                      ) : (
                        <>
                          <button
                            type="button"
                            className={`chat-widget__feedback-button${selectedFeedback === "up" ? " is-selected" : ""
                              }`}
                            onClick={() => handleRateMessage(message.id, "up")}
                            aria-label="Oceń pozytywnie"
                            aria-pressed={selectedFeedback === "up"}
                          >
                            👍
                          </button>
                          <button
                            type="button"
                            className={`chat-widget__feedback-button${selectedFeedback === "down" ? " is-selected" : ""
                              }`}
                            onClick={() => handleRateMessage(message.id, "down")}
                            aria-label="Oceń negatywnie"
                            aria-pressed={selectedFeedback === "down"}
                          >
                            👎
                          </button>
                        </>
                      )}
                    </div>
                  )}
                </div>
              );
            })}
            <div ref={messagesEndRef} />
          </div>

          {errorMessage && <p className="chat-widget__error">{errorMessage}</p>}

          <form className="chat-widget__form" onSubmit={handleSubmit}>
            {shouldShowSuggestions && (
              <div
                className="chat-widget__suggestions"
                aria-label="Podpowiedzi"
                ref={suggestionsRef}
              >
                {suggestions.map(text => (
                  <SuggestionBubble
                    key={text}
                    text={text}
                    onClick={() => handleSuggestionClick(text)}
                    onMouseDown={handleSuggestionMouseDown}
                  />
                ))}
              </div>
            )}
            <textarea
              ref={initialFocusRef}
              className="chat-widget__input"
              value={inputValue}
              onChange={event => setInputValue(event.target.value)}
              placeholder="Napisz wiadomość..."
              rows={2}
              disabled={isSending}
              onFocus={handleInputFocus}
              onBlur={handleInputBlur}
            />
            <button type="submit" className="chat-widget__submit" disabled={!canSubmit}>
              {isSending ? "Wysyłanie..." : "Wyślij"}
            </button>
          </form>
        </div>
      </div>
    </div>,
    document.body,
  );
};
