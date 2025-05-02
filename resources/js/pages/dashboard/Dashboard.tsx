import React, { useMemo } from "react";
import { Box, Grid, Card, CardContent, Drawer } from "@mui/material";
import { useMediaQuery } from "@mui/material";
import Sidebar from "../Sidebar";
import { useAirQuality } from "../../data/useAirQuality";
import { useWeatherConditions } from "../../data/useWeatherConditions";
import { getCityLatLng } from "../../utils/airQuality";
import { SearchAppBar } from "./SearchAppBar";
import { WeatherCard } from "./WeatherCard";
import { AirPollutionChart } from "./AirPollutionChart";
import { CityMap } from "./CityMap";

export const Dashboard: React.FC = () => {
  const [searchValue, setSearchValue] = React.useState("");
  const [city, setCity] = React.useState("Szczecin");
  const isMobile = useMediaQuery("(max-width:900px)");
  const { data: airData, loading: airLoading } = useAirQuality(city);
  const { weather, loading: weatherLoading } = useWeatherConditions(city);
  const coords = getCityLatLng(city);
  const todayStr = new Date().toLocaleDateString("en-GB", {
    weekday: "short",
    day: "numeric",
    month: "short",
    year: "numeric",
  });

  const canSearch = useMemo(
    () =>
      searchValue.trim().length > 0 &&
      Boolean(getCityLatLng(searchValue.trim())),
    [searchValue],
  );

  const handleSearch = () => {
    if (canSearch) {
      setCity(searchValue.trim());
    }
  };

  return (
    <Box
      sx={{
        display: "flex",
        backgroundColor: "#1e1e1e",
        minHeight: "100vh",
        color: "#fff",
      }}>
      {isMobile ? (
        <Drawer
          open={false}
          onClose={() => {}}
          ModalProps={{ keepMounted: true }}>
          <Sidebar />
        </Drawer>
      ) : (
        <Box sx={{ width: 240, flexShrink: 0 }}>
          <Sidebar />
        </Box>
      )}

      <Box sx={{ flexGrow: 1, p: 2 }}>
        <SearchAppBar
          searchValue={searchValue}
          onSearchChange={setSearchValue}
          onSearchSubmit={handleSearch}
        />

        <Grid container spacing={3} sx={{ mt: 1 }}>
          <Grid item xs={12} md={6}>
            <WeatherCard
              city={city}
              dateStr={todayStr}
              weather={weather}
              loading={weatherLoading}
            />
          </Grid>
          <Grid item xs={12} md={6}>
            <Card
              sx={{
                backgroundColor: "#222",
                color: "#fff",
                p: 2,
                borderRadius: 2,
              }}>
              <CardContent>
                <AirPollutionChart measurements={airData?.measurements} />
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12} md={6}>
            <Card sx={{ height: 400, borderRadius: 2 }}>
              <CityMap coords={coords} city={city} />
            </Card>
          </Grid>
        </Grid>
      </Box>
    </Box>
  );
};

export default Dashboard;
