import { useEffect, useState } from "react";
import { AirQualityResponse, getAirQuality } from "@/api/airQualityApi";

export const useAirQuality = (city: string) => {
  const [data, setData] = useState<AirQualityResponse | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);

      const r = await getAirQuality(city);
      setData(r);

      console.log(`API Response dla ${city}:`, r);

      setLoading(false);
    };

    fetchData();
  }, [city]);

  return { data, loading };
};
