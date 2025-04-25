import { useEffect, useState } from "react";
import { instance } from "../js/api/api";

import { cityCoordinates } from "./cities";

const buildCityToStationMap = (): Record<string, string> => {
  return Object.keys(cityCoordinates).reduce((map, city) => {
    const key = city
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .toLowerCase()
      .replace(/ /g, "");
    map[key] = city;
    return map;
  }, {} as Record<string, string>);
};

const cityToStation = buildCityToStationMap();

const getStationName = (city: string): string => {
  const key = city
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/ /g, "");

  const mapped = cityToStation[key] || city;

  return mapped
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ /g, "");
};

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
        const station = getStationName(city);
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
