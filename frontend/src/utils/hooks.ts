import { useState, useEffect } from "react";
import { checkHealth } from "../services/healthService";

/**
 * Custom hooks for common patterns
 */

/**
 * Debounce hook for search inputs
 */
export const useDebounce = <T>(value: T, delay: number): T => {
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
};

/**
 * Periodically checks API health and exposes a boolean flag.
 */
export const useApiHealth = (intervalMs: number = 60000): boolean => {
  const [isHealthy, setIsHealthy] = useState(true);

  useEffect(() => {
    let isMounted = true;

    const ping = async () => {
      try {
        const result = await checkHealth();
        if (!isMounted) return;
        setIsHealthy(result.success === true);
      } catch {
        if (!isMounted) return;
        setIsHealthy(false);
      }
    };

    // Initial ping
    ping();

    // Periodic ping
    const id = window.setInterval(ping, intervalMs);

    return () => {
      isMounted = false;
      window.clearInterval(id);
    };
  }, [intervalMs]);

  return isHealthy;
};

