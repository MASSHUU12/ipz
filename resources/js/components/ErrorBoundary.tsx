import React, { ErrorInfo, ReactNode } from "react";

interface ErrorBoundaryProps {
  children: ReactNode;
  fallback?: ReactNode;
  message?: string;
  renderFallback?: (error: Error, info: ErrorInfo) => ReactNode;
}

interface ErrorBoundaryState {
  hasError: boolean;
  error: Error | null;
  info: ErrorInfo | null;
}

export class ErrorBoundary extends React.Component<
  ErrorBoundaryProps,
  ErrorBoundaryState
> {
  constructor(props: ErrorBoundaryProps) {
    super(props);
    this.state = { hasError: false, error: null, info: null };
  }

  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { hasError: true, error, info: null };
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error("Uncaught error:", error, info);
    this.setState({ info });
  }

  render() {
    const { hasError, error, info } = this.state;

    if (hasError) {
      if (this.props.fallback) {
        return this.props.fallback;
      }

      if (this.props.renderFallback && error && info) {
        return this.props.renderFallback(error, info);
      }

      if (this.props.message) {
        return <p>{this.props.message}</p>;
      }

      return <p>Something went wrong (see console).</p>;
    }

    return this.props.children;
  }
}
