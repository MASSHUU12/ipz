import { useEffect, useState } from "react";
import { getSynop, SynopResponse } from "@/api/synopApi";

export const useWeatherConditions = (city: string) => {
  const [weather, setWeather] = useState<SynopResponse | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchWeather = async () => {
      setLoading(true);

      const res = await getSynop(city);

      console.log(res);

      setWeather(res);

      setLoading(false);
    };

    fetchWeather();
  }, [city]);

  return { weather, loading };
};
