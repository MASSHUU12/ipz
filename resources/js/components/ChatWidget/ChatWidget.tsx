import { FormEvent, useEffect, useMemo, useRef, useState } from "react";
import { createPortal } from "react-dom";
import { sendChatWidgetMessage } from "../../api/chatWidgetApi";
import { ChatIcon } from "./ChatIcon";
import "./ChatWidget.css";

type MessageRole = "user" | "assistant";

interface ChatMessage {
  id: string;
  role: MessageRole;
  content: string;
}

const hiddenClass = "chat-widget--hidden";

const buildId = () => Math.random().toString(36).slice(2);

export const ChatWidget = () => {
  const [mounted, setMounted] = useState(false);
  const [isOpen, setIsOpen] = useState(false);
  const [isSending, setIsSending] = useState(false);
  const [inputValue, setInputValue] = useState("");
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const messagesEndRef = useRef<HTMLDivElement | null>(null);
  const initialFocusRef = useRef<HTMLTextAreaElement | null>(null);

  useEffect(() => {
    setMounted(true);

    return () => {
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

  const toggleWidget = () => {
    setIsOpen(prev => !prev);
    setErrorMessage(null);
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
      const data = await sendChatWidgetMessage({ message: trimmed });

      if (!data) {
        throw new Error("Nie udało się połączyć z serwerem.");
      }

      const assistantText = data.response ?? "Brak odpowiedzi od serwera.";

      const assistantMessage: ChatMessage = {
        id: buildId(),
        role: "assistant",
        content: assistantText,
      };

      setMessages(prev => [...prev, assistantMessage]);
    } catch (error) {
      const fallbackMessage =
        error instanceof Error ? error.message : "Wystąpił nieoczekiwany błąd.";

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
          <button
            type="button"
            className="chat-widget__close"
            aria-label="Zamknij czat"
            onClick={toggleWidget}
          >
            ×
          </button>
        </header>

        <div className="chat-widget__body">
          <div className="chat-widget__messages">
            {messages.length === 0 && (
              <div className="chat-widget__empty">
                <p>Zadaj pytanie, a spróbujemy pomóc.</p>
              </div>
            )}

            {messages.map(message => (
              <div
                key={message.id}
                className={`chat-widget__message chat-widget__message--${message.role}`}
              >
                <span>{message.content}</span>
              </div>
            ))}
            <div ref={messagesEndRef} />
          </div>

          {errorMessage && <p className="chat-widget__error">{errorMessage}</p>}

          <form className="chat-widget__form" onSubmit={handleSubmit}>
            <textarea
              ref={initialFocusRef}
              className="chat-widget__input"
              value={inputValue}
              onChange={event => setInputValue(event.target.value)}
              placeholder="Napisz wiadomość..."
              rows={2}
              disabled={isSending}
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
