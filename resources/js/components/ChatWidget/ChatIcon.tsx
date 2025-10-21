import chatWidgetIconUrl from "./chat-widget-icon.svg";

interface ChatIconProps {
  className?: string;
}

export const ChatIcon = ({ className }: ChatIconProps) => (
  <img src={chatWidgetIconUrl} className={className} alt="" aria-hidden="true" />
);
