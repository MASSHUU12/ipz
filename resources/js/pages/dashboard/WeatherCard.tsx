import { Card, CardContent, Typography, Box } from "@mui/material";
import { SynopResponse } from "@/api/synopApi";
import { useEffect, useState } from "react";

interface Props {
  city: string;
  dateStr: string;
  weather: SynopResponse | null;
  loading: boolean;
}

enum Unit {
  Celsius,
  Fahrenheit,
}

export function WeatherCard({
  city,
  dateStr,
  weather,
  loading,
}: Props): JSX.Element {
  const [temperature, setTemperature] = useState<number>(
    weather?.temperature ?? 0,
  );
  const [unit, setUnit] = useState<Unit>(Unit.Celsius);

  const toggleUnit = () => {
    setUnit(unit === Unit.Celsius ? Unit.Fahrenheit : Unit.Celsius);
  };

  useEffect(() => {
    switch (unit) {
      case Unit.Celsius:
        setTemperature(weather?.temperature ?? 0);
        break;
      case Unit.Fahrenheit:
        setTemperature((weather?.temperature ?? 0) * 1.8 + 32);
        break;
    }
  }, [weather?.temperature, unit]);

  return (
    <Card
      sx={{
        backgroundColor: "#222",
        color: "#fff",
        borderRadius: 2,
        height: "100%",
      }}>
      <CardContent>
        <Typography variant="h6">{dateStr}</Typography>
        <Typography variant="subtitle1">{city}</Typography>
        {loading || !weather ? (
          <Typography>Loading...</Typography>
        ) : (
          <Box mt={2} textAlign="center">
            <button
              onClick={toggleUnit}
              aria-label="Toggle temperature unit"
              style={{
                all: "unset",
                cursor: "pointer",
                display: "inline-block",
              }}>
              <Typography variant="h4">
                🌤 {temperature}°{unit === Unit.Celsius ? "C" : "F"}
              </Typography>
            </button>
            <Typography>💧 {weather.relative_humidity}%</Typography>
            <Typography>💨 {weather.wind_speed} m/s</Typography>
            <Typography>🌡 {weather.pressure} hPa</Typography>
          </Box>
        )}
      </CardContent>
    </Card>
  );
}
