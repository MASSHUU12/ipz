import { useCallback, useEffect, useState } from "react";
import { AirQualityResponse, getAirQuality } from "@/api/airQualityApi";

export const useAirQuality = (city: string) => {
  const [data, setData] = useState<AirQualityResponse | null>(null);
  const [loading, setLoading] = useState(true);

  const fetchData = useCallback(async () => {
    setLoading(true);

    const r = await getAirQuality(city);
    setData(r);

    console.log(`API Response for ${city}:`, r);

    setLoading(false);
  }, [city]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  return { data, loading, refetch: fetchData };
};
