import { useEffect, useState } from "react";
import { cityCoordinates } from "./cities";

const API_URL = import.meta.env.VITE_API_URL || "http://localhost:8000/api";

export interface AirQualityData {
  airQuality: {
    index: string;
  };
  measurements: {
    parameter: string;
    value: number;
    unit: string;
    measurementTime: string;
  }[];
}

export const useAirQuality = (city: string) => {
  const [data, setData] = useState<AirQualityData | null>(null);
  const [loading, setLoading] = useState(true);
  const coords = cityCoordinates[city];

  useEffect(() => {
    if (!coords) return;

    const url = `${API_URL}/air-quality?lat=${coords.lat}&lon=${coords.lon}`;
    setLoading(true);

    fetch(url)
      .then((res) => res.text())
      .then((text) => {
        if (text.includes("namespace")) throw new Error("Wrong answer from backend");

        const json = JSON.parse(text);
        if (!json?.data) throw new Error("no 'data'");

        setData(json.data);
      })
      .catch((err) => {
        console.error("❌ API error:", err.message || err);
        setData(null);
      })
      .finally(() => setLoading(false));
  }, [city]);

  return { data, loading };
};
