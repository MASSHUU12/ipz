import React from "react";
import { Card, CardContent, Typography, Box } from "@mui/material";
import { SynopResponse } from "@/api/synopApi";

interface Props {
  city: string;
  dateStr: string;
  weather: SynopResponse | null;
  loading: boolean;
}

export const WeatherCard: React.FC<Props> = ({
  city,
  dateStr,
  weather,
  loading,
}) => {
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
            <Typography variant="h4">🌤 {weather.temperature}°C</Typography>
            <Typography>💧 {weather.relative_humidity}%</Typography>
            <Typography>💨 {weather.wind_speed} m/s</Typography>
            <Typography>🌡 {weather.pressure} hPa</Typography>
          </Box>
        )}
      </CardContent>
    </Card>
  );
};
