import { useEffect, useState } from "react";
import { instance } from "@/api/api";

export const useWeatherConditions = (city: string) => {
  const [weather, setWeather] = useState<null | {
    temperature: number;
    humidity: number;
    wind_speed: number;
    pressure: number;
  }>(null);

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchWeather = async () => {
      setLoading(true);
      try {
        const station = city
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "")
          .replace(/ /g, "");
        const res = await instance.get(`/synop?station=${station}`);
        const entry = res.data;
        if (entry && entry.temperatura) {
          setWeather({
            temperature: parseFloat(entry.temperatura),
            humidity: parseFloat(entry.wilgotnosc_wzgledna),
            wind_speed: parseFloat(entry.predkosc_wiatru),
            pressure: parseFloat(entry.cisnienie),
          });
        } else {
          setWeather(null);
        }
      } catch (error) {
        setWeather(null);
      } finally {
        setLoading(false);
      }
    };

    fetchWeather();
  }, [city]);

  return { weather, loading };
};
