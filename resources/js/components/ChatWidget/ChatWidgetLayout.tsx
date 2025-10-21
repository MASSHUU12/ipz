import { PropsWithChildren } from "react";
import { usePage } from "@inertiajs/react";
import { ChatWidget } from "./ChatWidget";

const HIDDEN_COMPONENTS = new Set(["login", "register"]);

export const ChatWidgetLayout = ({ children }: PropsWithChildren) => {
  const { component } = usePage();
  const shouldHide = HIDDEN_COMPONENTS.has(component?.toLowerCase?.() ?? "");

  return (
    <>
      {children}
      {!shouldHide && <ChatWidget />}
    </>
  );
};
