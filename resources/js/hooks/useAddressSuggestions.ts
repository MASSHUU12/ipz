import { useState, useEffect } from "react";
import { suggestAddresses } from "@/api/addressApi";
import { useDebounce } from "@/hooks/useDebounce";

export function useAddressSuggestions(query: string) {
  const debounced = useDebounce(query, 300);
  const [options, setOptions] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!debounced.trim()) {
      setOptions([]);
      setLoading(false);
      return;
    }
    setLoading(true);
    suggestAddresses(debounced)
      .then(data => {
        setOptions(data?.suggestions ?? []);
      })
      .catch(console.error)
      .finally(() => setLoading(false));
  }, [debounced]);

  return { options, loading };
}
