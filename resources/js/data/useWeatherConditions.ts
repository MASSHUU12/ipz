import { useEffect, useState } from "react";
import { getSynop, SynopDataConverted } from "@/api/synopApi";

export const useWeatherConditions = (city: string) => {
  const [weather, setWeather] = useState<SynopDataConverted | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchWeather = async () => {
      setLoading(true);

      const res = await getSynop(city);

      console.log(res);

      if (res) {
        setWeather({
          ...res,
          temperature: parseFloat(res.temperatura),
          wind_speed: parseFloat(res.predkosc_wiatru),
          wind_direction: parseFloat(res.kierunek_wiatru),
          relative_humidity: parseFloat(res.wilgotnosc_wzgledna),
          rainfall_total: parseFloat(res.suma_opadu),
          pressure: parseFloat(res.cisnienie),
        });
      } else {
        setWeather(null);
      }

      setLoading(false);
    };

    fetchWeather();
  }, [city]);

  return { weather, loading };
};
