import { useEffect, useState } from "react";
import { cityCoordinates } from "./cities";
import { instance } from "@/api/api";

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

export const fetchAirQualityForCity = async (city: string) => {
  const coords = cityCoordinates[city];
  if (!coords) return null;

  try {
    const res = await instance.get(
      `/air-quality?lat=${coords.lat}&lon=${coords.lon}`,
    );
    const responseText = res.data;

    const json =
      typeof responseText === "string"
        ? JSON.parse(responseText)
        : responseText;
    return { city, data: json.data };
  } catch (err) {
    console.error(`Błąd dla ${city}:`, err);
    return null;
  }
};

export const useAirQuality = (city: string) => {
  const [data, setData] = useState<AirQualityData | null>(null);
  const [loading, setLoading] = useState(true);
  const coords = cityCoordinates[city];

  useEffect(() => {
    if (!coords) return;

    const fetchData = async () => {
      setLoading(true);
      try {
        const res = await instance.get(
          `/air-quality?lat=${coords.lat}&lon=${coords.lon}`,
        );
        const responseText = res.data;
        console.log(`API Response dla ${city}:`, responseText);
        if (
          typeof responseText === "string" &&
          responseText.includes("namespace")
        ) {
          throw new Error("Wrong answer from backend");
        }

        const json =
          typeof responseText === "string"
            ? JSON.parse(responseText)
            : responseText;

        if (!json?.data) throw new Error("no 'data'");
        setData(json.data);
      } catch (err: any) {
        console.error("API error:", err.message || err);
        setData(null);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [city]);

  return { data, loading };
};
