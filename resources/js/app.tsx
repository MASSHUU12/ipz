import "../css/app.css";

import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createRoot } from "react-dom/client";
import { initializeTheme } from "./hooks/use-appearance";
import { ChatWidgetLayout } from "./components/ChatWidget";
import type { ComponentType, ReactNode } from "react";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: async name => {
    const page = (await resolvePageComponent(
      `./pages/${name}.tsx`,
      import.meta.glob("./pages/**/*.tsx"),
    )) as {
      default: ComponentType & {
        layout?: ((page: ReactNode) => ReactNode) | ReactNode;
        __chatWidgetApplied?: boolean;
      };
      [key: string]: unknown;
    };

    const component = page.default;
    if (component && !component.__chatWidgetApplied) {
      const existingLayout = component.layout;

      if (!existingLayout) {
        component.layout = pageChildren => <ChatWidgetLayout>{pageChildren}</ChatWidgetLayout>;
      } else if (typeof existingLayout === "function") {
        const originalLayout = existingLayout;
        component.layout = pageChildren => (
          <ChatWidgetLayout>{originalLayout(pageChildren)}</ChatWidgetLayout>
        );
      } else {
        const OriginalLayout = existingLayout;
        component.layout = pageChildren => (
          <ChatWidgetLayout>
            {OriginalLayout}
            {pageChildren}
          </ChatWidgetLayout>
        );
      }

      component.__chatWidgetApplied = true;
    }

    return page;
  },
  setup({ el, App, props }) {
    const root = createRoot(el);

    root.render(<App {...props} />);
  },
  progress: {
    color: "#4B5563",
  },
});

// This will set light / dark mode on load...
initializeTheme();
