import { ButtonHTMLAttributes } from "react";

interface SuggestionBubbleProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  text: string;
}

export const SuggestionBubble = ({ text, className = "", ...rest }: SuggestionBubbleProps) => {
  return (
    <button
      type="button"
      className={`chat-widget__suggestion-bubble${className ? ` ${className}` : ""}`}
      {...rest}
    >
      {text}
    </button>
  );
};
