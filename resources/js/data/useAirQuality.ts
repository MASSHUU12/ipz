import { useEffect, useState } from "react";
import { AirQualityResponse, getAirQuality } from "@/api/airQualityApi";

export const useAirQuality = (city: string) => {
  const [data, setData] = useState<AirQualityResponse | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);

      setData(await getAirQuality(city));

      console.log(`API Response dla ${city}:`, data);

      setLoading(false);
    };

    fetchData();
  }, [city, data]);

  return { data, loading };
};
