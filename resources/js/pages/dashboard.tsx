import React from "react";
import {
  AppBar,
  Toolbar,
  Typography,
  Card,
  Grid,
  CardContent,
  TextField,
  IconButton,
  InputAdornment,
  Avatar,
  useMediaQuery,
  Drawer,
  Box,
} from "@mui/material";
import { Notifications, ArrowDropDown, Search as SearchIcon } from "@mui/icons-material";
import { ResponsiveContainer, BarChart, Bar, XAxis, YAxis, Tooltip, CartesianGrid, Cell } from "recharts";

import Sidebar from "./Sidebar";
import AirQualityBar from "./components/AirQualityBar";
import { cityCoordinates } from "../../data/cities";
import { useAirQuality } from "../../data/useAirQuality";
import { useWeatherConditions } from "../../data/useWeatherConditions";
const airPollutionData = [
  { name: "Jan", value: 100 },
  { name: "Feb", value: 200 },
  { name: "Mar", value: 150 },
  { name: "Apr", value: 300 },
  { name: "May", value: 250 },
  { name: "Jun", value: 400 },
  { name: "Jul", value: 500 },
  { name: "Aug", value: 350 },
  { name: "Sep", value: 450 },
];

const weatherData = [
  { day: "Mon", value: 80 },
  { day: "Tue", value: 120 },
  { day: "Wed", value: 200 },
  { day: "Thu", value: 180 },
  { day: "Fri", value: 100 },
  { day: "Sat", value: 140 },
  { day: "Sun", value: 200 },
];
const Dashboard = () => {
  const [searchValue, setSearchValue] = React.useState("");
  const isMobile = useMediaQuery("(max-width:900px)");
  const [selectedCity, setSelectedCity] = React.useState("Szczecin");
  const { data: airData, loading: airLoading } = useAirQuality(selectedCity);
  const { weather, loading: weatherLoading } = useWeatherConditions(selectedCity);

  const normalizeParameter = (label: string): string => {
    const mapping: Record<string, string> = {
      "pył zawieszony pm10": "pm10",
      "pył zawieszony pm2.5": "pm25",
      "dwutlenek azotu": "no2",
      "dwutlenek siarki": "so2",
      "ozon": "o3",
      "tlenek węgla": "co",
    };
    return mapping[label.toLowerCase()] || label.toLowerCase();
  };

  const pollutantThresholds: Record<string, { good: number; moderate: number; unhealthy: number }> = {
    pm25: { good: 10, moderate: 25, unhealthy: 50 },
    pm10: { good: 20, moderate: 50, unhealthy: 100 },
    no2: { good: 40, moderate: 100, unhealthy: 200 },
    so2: { good: 50, moderate: 125, unhealthy: 300 },
    o3: { good: 60, moderate: 120, unhealthy: 180 },
    co: { good: 3, moderate: 6, unhealthy: 10 },
  };

  const getAirQualityLevel = (parameter: string, rawValue: number | string) => {
    const value = typeof rawValue === "string" ? parseFloat(rawValue) : rawValue;
    const norm = pollutantThresholds[parameter.toLowerCase()];
    if (!norm) return { level: "Unknown", color: "#999" };
    if (value <= norm.good) return { level: "Good", color: "#00e676" };
    if (value <= norm.moderate) return { level: "Moderate", color: "#ffeb3b" };
    if (value <= norm.unhealthy) return { level: "Unhealthy", color: "#ff9800" };
    return { level: "Very Unhealthy", color: "#f44336" };
  };

  const airPollutionChartData = airData?.measurements?.map((m) => {
    const param = normalizeParameter(m.parameter);
    const { level, color } = getAirQualityLevel(param, m.value);
    return {
      name: param.toUpperCase(),
      value: parseFloat(m.value.toString()),
      level,
      color,
    };
  }) ?? [];

  return (
    <Box sx={{ display: "flex", backgroundColor: "#1e1e1e", minHeight: "100vh", color: "#fff" }}>
      {isMobile ? (
        <Drawer
          variant="temporary"
          open={false}
          onClose={() => {}}
          ModalProps={{ keepMounted: true }}
        >
          <Sidebar />
        </Drawer>
      ) : (
        <Box sx={{ width: 240, minWidth: 200, flexShrink: 0 }}>
          <Sidebar />
        </Box>
      )}

      <Box sx={{ flexGrow: 1, width: "100%", px: 2, py: 2, display: "flex", justifyContent: "center" }}>
        <Box sx={{ maxWidth: { xs: 1400 }, width: "100%", mx: "auto" }}>
          <AppBar position="static" sx={{ mb: 2, backgroundColor: "#1e1e1e", color: "#fff", p: 1 }}>
            <Toolbar sx={{ display: "flex", justifyContent: "space-between" }}>
              <TextField
                placeholder="Search city"
                variant="outlined"
                size="small"
                sx={{ backgroundColor: "#2e2e2e", borderRadius: "10px", input: { color: "#fff" }, flexGrow: 1,}}
                value={searchValue}
                onChange={(e) => setSearchValue(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === "Enter" && cityCoordinates[searchValue]) {
                    setSelectedCity(searchValue);
                  }
                }}
                InputProps={{
                  endAdornment: (
                    <InputAdornment position="end">
                      <IconButton
                        onClick={() => {
                          if (cityCoordinates[searchValue]) {
                            setSelectedCity(searchValue);
                          }
                        }}
                        sx={{ color: "#fff", boxShadow: 3 }}
                      >
                        <SearchIcon />
                      </IconButton>
                    </InputAdornment>
                  ),
                }}
              />
              <Box sx={{ display: "flex", alignItems: "center", ml: 2 }}>
                <IconButton sx={{ color: "#fff", ml: 1 }}>
                  <Notifications />
                </IconButton>
                <IconButton sx={{ color: "#fff" }}>
                  <Avatar src="/path-to-profile-pic.jpg" />
                </IconButton>
                <IconButton sx={{ color: "#fff" }}>
                  <ArrowDropDown />
                </IconButton>
              </Box>
            </Toolbar>
          </AppBar>


            {isMobile ? (
    <>
      {/* Pogoda */}
      <Grid container spacing={3} alignItems="stretch"> 
                {/* Pogoda */}
                <Grid item xs={12} md={6}>
                  <Box sx={{ height: "80%" }}>
                    <Card sx={{ height: "90%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: {xs:2,mb:0 }}}>
                      <CardContent sx={{ flexGrow: 1 }}>
                        <Typography variant="h6">Mon, 9 Mar, 2025</Typography>
                        <Typography variant="subtitle1">{selectedCity}</Typography>
                        <Box display="flex" justifyContent="space-between" mt={2}>
                          <Typography variant="h4" fontWeight="bold">🌤 12°C</Typography>
                        </Box>
                      </CardContent>
                    </Card>
                  </Box>
                </Grid>

                {/* Podsumowanie pogody */}
                <Grid item xs={12} md={6}>
                  <Box sx={{ height: "80%" }}>
                    <Card sx={{ height: "90%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
                      <CardContent sx={{ flexGrow: 1 }}>
                        <Typography variant="h6">Weather Summary</Typography>
                        <ResponsiveContainer width="100%" height={150}>
                          <BarChart data={weatherData}>
                            <XAxis dataKey="day" />
                            <YAxis />
                            <Tooltip />
                            <Bar dataKey="value" fill="#00c8ff" radius={[10, 10, 0, 0]} />
                          </BarChart>
                        </ResponsiveContainer>
                      </CardContent>
                    </Card>
                  </Box>
                </Grid>

                {/* Jakość powietrza */}
                <Grid item xs={12} md={6}>
                  <Box sx={{ height: "80%" }}>
                    <Card sx={{ height: "90%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
                    <CardContent sx={{ flexGrow: 1 }}>
                      <Typography variant="h6" gutterBottom>Today's Air Pollution</Typography>
                      {airLoading ? (
                        <Typography>Loading...</Typography>
                      ) : airData ? (
                        <>
                          <Typography variant="subtitle2" gutterBottom>
                            Quality Index: {airData.airQuality.index}
                          </Typography>
                          <Box sx={{ maxWidth: 300, mx: "auto" }}>
                            <AirQualityBar index={airData.airQuality.index} />
                          </Box>
                          <Box mt={2}>
                            <Typography variant="body2">Measuerement details:</Typography>
                            <ul style={{ paddingLeft: 16 }}>
                              {airData.measurements.map((m: any, i: number) => (
                                <li key={i}>
                                  {m.parameter}: {m.value} {m.unit}
                                </li>
                              ))}
                            </ul>
                          </Box>
                        </>
                      ) : (
                        <Typography>No data</Typography>
                      )}
                    </CardContent>

                    </Card>
                  </Box>
                </Grid>

                {/* Wykres powietrza */}
                <Grid item xs={12} md={6}>
                  <Box sx={{ height: "80%" }}>
                    <Card sx={{ height: "90%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
                      <CardContent sx={{ flexGrow: 1 }}>
                        <Typography variant="h6">Overview</Typography>
                        <ResponsiveContainer width="100%" height={250}>
                        <BarChart data={airPollutionChartData}>
                        <CartesianGrid strokeDasharray="3 3" stroke="#444" />
                        <XAxis dataKey="name" stroke="#ccc" angle={-15} textAnchor="end" interval={0} />
                        <YAxis stroke="#ccc" />
                        <Tooltip
                          contentStyle={{ backgroundColor: "#333", border: "none", borderRadius: 8 }}
                          labelStyle={{ color: "#fff" }}
                          itemStyle={{ color: "#00c8ff" }}
                        />
                        <Bar dataKey="value" fill="#00c8ff" radius={[10, 10, 0, 0]} />
                      </BarChart>

                        </ResponsiveContainer>


                      </CardContent>
                    </Card>
                  </Box>
                </Grid>
              </Grid>
    </>
  ) : (
    <Box sx={{ backgroundColor: "#1e1e1e",boxShadow:5, p: 2, borderRadius: 2 }}>
    <Grid container spacing={3}>
      {/* Pogoda */}
      <Grid item xs={12} md={6}>
        <Card
          sx={{
            height: "80%",
            display: "flex",
            flexDirection: "column",
            backgroundColor: "#222",
            color: "#fff",
            borderRadius: 2,
            p: 2,
          }}
        >
          <CardContent sx={{ flexGrow: 1 }}>
            <Typography variant="h6">
              {new Date().toLocaleDateString("en-GB", {
                weekday: "short",
                day: "numeric",
                month: "short",
                year: "numeric",
              })}
            </Typography>
  
            <Typography variant="subtitle1">{selectedCity}</Typography>
            <Box mt={2} display="flex" flexDirection="column" alignItems="center">
              <Typography variant="h4" fontWeight="bold">
                🌤 {weatherLoading || !weather ? "--" : `${weather.temperature}°C`}
              </Typography>
            </Box>
          </CardContent>
        </Card>
      </Grid>

  
      {/* Podsumowanie pogody */}
      <Grid item xs={12} md={6}>
        <Card sx={{ height: "80%", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
          <CardContent sx={{ flexGrow: 1 }}>
            <Typography variant="h6">Weather Summary</Typography>
            <ResponsiveContainer width="100%" height={150}>
              <BarChart data={weatherData}>
                <XAxis dataKey="day" />
                <YAxis />
                <Tooltip />
                <Bar dataKey="value" fill="#00c8ff" radius={[10, 10, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </Grid>
  
      {/* Jakość powietrza */}
      <Grid item xs={12} md={6}>
        <Card sx={{ height: "80%", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
          <CardContent sx={{ flexGrow: 1 }}>
            <Typography variant="h6" gutterBottom>Overview</Typography>

            {weatherLoading || !weather ? (
              <Typography>Loading Data...</Typography>
            ) : (
              <Box>
                <Typography variant="body1" component="div" sx={{ mb: 1 }}>
                  💧 Humidity: {weather.humidity}%
                </Typography>
                <Typography variant="body1" component="div" sx={{ mb: 1 }}>
                  💨 Wind: {weather.wind_speed} m/s
                </Typography>
                <Typography variant="body1" component="div">
                  🌡 Pressure: {weather.pressure} hPa
                </Typography>
              </Box>
            )}
          </CardContent>

        </Card>
      </Grid>
  
      {/* Wykres powietrza */}
      <Grid item xs={12} md={6}>
        <Card sx={{ height: "80%", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
          <CardContent sx={{ flexGrow: 1 }}>
            <Typography variant="h6">Today's Air Pollution</Typography>
            <ResponsiveContainer width="100%" height={250}>
              <BarChart
                data={airPollutionChartData}
                margin={{ top: 20, right: 30, left: 0, bottom: 5 }}
              >
                <CartesianGrid strokeDasharray="3 3" stroke="#444" />
                <XAxis
                  dataKey="name"
                  stroke="#ccc"
                  interval={0}
                  tick={{ fontSize: 12 }}
                />
                <YAxis stroke="#ccc" />
                <Tooltip
                  formatter={(value: number, name: string, props: any) => {
                    const { payload } = props;
                    return [`${value} µg/m³ – ${payload.level}`];
                  }}
                  contentStyle={{ backgroundColor: "#333", borderRadius: 8 }}
                  labelStyle={{ color: "#fff" }}
                  itemStyle={{ color: "#fff" }}
                />
                <Bar
                  dataKey="value"
                  radius={[8, 8, 0, 0]}
                  label={{ position: "top", fill: "#fff", fontSize: 12 }}
                >
                  {airPollutionChartData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color || "#888"} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </Grid>
    </Grid>
  </Box>
  
          )}
          </Box>
        </Box>
      </Box>
    );
  };

  export default Dashboard;
