import React from "react";
import { Card, CardContent, Typography, Box } from "@mui/material";

interface Weather {
  temperature: number;
  humidity: number;
  wind_speed: number;
  pressure: number;
}

interface Props {
  city: string;
  dateStr: string;
  weather: Weather | null;
  loading: boolean;
}

export const WeatherCard: React.FC<Props> = ({
  city,
  dateStr,
  weather,
  loading,
}) => (
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
          <Typography variant="h4">🌤 {weather.temperature}°C</Typography>
          <Typography>💧 {weather.humidity}%</Typography>
          <Typography>💨 {weather.wind_speed} m/s</Typography>
          <Typography>🌡 {weather.pressure} hPa</Typography>
        </Box>
      )}
    </CardContent>
  </Card>
);
