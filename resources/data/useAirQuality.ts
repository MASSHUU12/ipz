import { useEffect, useState } from "react";
import { instance } from "../js/api/api";
import { cityCoordinates } from "./cities";

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

    const fetchData = async () => {
      setLoading(true);
      try {
        const res = await instance.get(
          `/air-quality?lat=${coords.lat}&lon=${coords.lon}`
        );
        const responseText = res.data;
        if (typeof responseText === "string" && responseText.includes("namespace")) {
          throw new Error("Wrong answer from backend");
        }

        const json = typeof responseText === "string" ? JSON.parse(responseText) : responseText;
        
        if (!json?.data) throw new Error("no 'data'");
        setData(json.data);
      } catch (err: any) {
        console.error("❌ API error:", err.message || err);
        setData(null);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [city]);

  return { data, loading };
};
