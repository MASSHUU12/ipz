import React, { useEffect, useState } from "react";
import {
  Box,
  Card,
  Typography,
  CircularProgress,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
} from "@mui/material";
import { instance } from "../../js/api/api";
import Sidebar from "./Sidebar";
import pLimit from "p-limit";
const normalizeParameter = (label: string): string => {
  const mapping: Record<string, string> = {
    "pył zawieszony pm10": "pm10",
    "pył zawieszony pm2.5": "pm25",
    "particulate matter pm10": "pm10",
    "particulate matter pm2.5": "pm25",
    pm10: "pm10",
    "pm2.5": "pm25",
    no2: "no2",
    so2: "so2",
    o3: "o3",
    co: "co",
  };
  return mapping[label.toLowerCase()] || label.toLowerCase();
};

const parameterOptions = [
  { label: "PM10 (Particulate Matter)", value: "pm10" },
  { label: "PM2.5 (Particulate Matter)", value: "pm25" },
  { label: "Nitrogen Dioxide (NO₂)", value: "no2" },
  { label: "Sulfur Dioxide (SO₂)", value: "so2" },
  { label: "Ozone (O₃)", value: "o3" },
  { label: "Carbon Monoxide (CO)", value: "co" },
];

interface Measurement {
  parameter: string;
  value: number;
  unit: string;
  measurementTime: string;
}

interface CityEntry {
  city: string;
  value: number;
}

const fetchValueForCity = async (
  city: string,
  lat: number,
  lon: number,
  parameter: string,
): Promise<CityEntry | null> => {
  try {
    const res = await instance.get(`/air-quality?lat=${lat}&lon=${lon}`, {
      timeout: 20000,
    });
    const json = typeof res.data === "string" ? JSON.parse(res.data) : res.data;
    const measurements: Measurement[] = json?.data?.measurements ?? [];

    const data = measurements.find(
      (m: Measurement) => normalizeParameter(m.parameter) === parameter,
    );

    if (!data || isNaN(Number(data.value))) {
      console.warn(`Brak danych ${parameter.toUpperCase()} dla ${city}`);
      console.log(
        `Miasto ${city} ma dostępne parametry:`,
        measurements.map(m => m.parameter),
      );

      return null;
    }

    return { city, value: Number(data.value) };
  } catch (err) {
    console.error(`Błąd pobierania danych dla ${city}:`, err);

    return null;
  }
};

const AirQualityRanking = () => {
  const [ranking, setRanking] = useState<CityEntry[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedParam, setSelectedParam] = useState<string>("pm10");

  useEffect(() => {
    const loadData = async () => {
      setLoading(true);
      const cities = Object.entries(cityCoordinates);
      const limit = pLimit(1);

      const limitedFetches = cities.map(([city, coords]) =>
        limit(() =>
          fetchValueForCity(city, coords.lat, coords.lon, selectedParam),
        ),
      );

      const results = await Promise.all(limitedFetches);
      const filtered = results.filter((r): r is CityEntry => r !== null);
      const sorted = filtered.sort((a, b) => b.value - a.value).slice(0, 10);

      setRanking(sorted);
      setLoading(false);
    };

    loadData();
  }, [selectedParam]);

  return (
    <Box
      sx={{
        display: "flex",
        minHeight: "100vh",
        backgroundColor: "#1e1e1e",
        color: "#fff",
      }}>
      <Box sx={{ width: 240, flexShrink: 0 }}>
        <Sidebar />
      </Box>

      <Box sx={{ flexGrow: 1, p: 4 }}>
        <Card
          sx={{
            backgroundColor: "#1e1e1e",
            color: "#fff",
            p: 4,
            boxShadow: "0 0 15px rgba(0,0,0,0.5)",
            borderRadius: "12px",
          }}>
          <Typography variant="h5" gutterBottom>
            Top 10 Cities by Parameter: {selectedParam.toUpperCase()}
          </Typography>

          <FormControl fullWidth sx={{ maxWidth: 300, my: 2 }}>
            <InputLabel id="param-label" sx={{ color: "#ccc" }}>
              Select parameter
            </InputLabel>
            <Select
              labelId="param-label"
              value={selectedParam}
              onChange={e => setSelectedParam(e.target.value)}
              sx={{ color: "#fff", backgroundColor: "#1e1e1e" }}>
              {parameterOptions.map(option => (
                <MenuItem key={option.value} value={option.value}>
                  {option.label}
                </MenuItem>
              ))}
            </Select>
          </FormControl>

          {loading ? (
            <CircularProgress color="inherit" />
          ) : ranking.length === 0 ? (
            <Typography>Brak danych dla wybranego parametru.</Typography>
          ) : (
            <Box
              sx={{ mt: 4, display: "flex", flexDirection: "column", gap: 2 }}>
              {ranking.map((entry, index) => (
                <Box
                  key={entry.city}
                  sx={{
                    px: 3,
                    py: 2,
                    backgroundColor: "#1f1f1f",
                    borderRadius: "10px",
                    boxShadow: "0 4px 12px rgba(0,0,0,0.3)",
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                    transition: "background-color 0.2s ease",
                    "&:hover": {
                      backgroundColor: "#2a2a2a",
                    },
                  }}>
                  <Typography variant="h6" sx={{ fontWeight: 500 }}>
                    <strong>{index + 1}.</strong> {entry.city}
                  </Typography>
                  <Typography
                    variant="h6"
                    sx={{ fontWeight: "bold", color: "#00c8ff" }}>
                    {entry.value.toFixed(1)} µg/m³
                  </Typography>
                </Box>
              ))}
            </Box>
          )}
        </Card>
      </Box>
    </Box>
  );
};

export default AirQualityRanking;
