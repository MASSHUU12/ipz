import React, { useEffect, useMemo } from "react";
import { Box, Grid, Card, CardContent, Drawer } from "@mui/material";
import { useMediaQuery } from "@mui/material";
import Sidebar from "../Sidebar";
import { useAirQuality } from "../../data/useAirQuality";
import { useWeatherConditions } from "../../data/useWeatherConditions";
import { SearchAppBar } from "./SearchAppBar";
import { WeatherCard } from "./WeatherCard";
import { AirPollutionChart } from "./AirPollutionChart";
import { CityMap } from "./CityMap";
import { LatLng } from "leaflet";
import { getCurrentUser } from "@/api/userApi";

export const Dashboard: React.FC = () => {
  const [searchValue, setSearchValue] = React.useState("");
  const [city, setCity] = React.useState("Szczecin, ul. Piłsudskiego");
  const [mobileOpen, setMobileOpen] = React.useState(false);
  const isMobile = useMediaQuery("(max-width:900px)");
  const {
    data: airData,
    loading: airLoading,
    refetch: refetchAirData,
  } = useAirQuality(city);
  const { weather, loading: weatherLoading } = useWeatherConditions(
    airData?.station.city ?? city,
  );
  const todayStr = new Date().toLocaleDateString("en-GB", {
    weekday: "short",
    day: "numeric",
    month: "short",
    year: "numeric",
  });

  const getCoords = (): LatLng | undefined => {
    return airData === null
      ? undefined
      : new LatLng(airData.station.latitude, airData.station.longitude);
  };

  const canSearch = useMemo(() => {
    return searchValue.trim().length > 0;
  }, [searchValue]);

  const handleSearch = () => {
    if (canSearch) {
      setCity(searchValue.trim());
    }
  };

  const handleDrawerToggle = () => {
    setMobileOpen(open => !open);
  };

  useEffect(() => {
    const id = setInterval(() => {
      refetchAirData();
      // refetchWeather();
    }, 300_000); // 300_000 = 5 minutes
    return () => clearInterval(id);
  }, [refetchAirData]);
  const [emailVerified, setEmailVerified] = React.useState(true);

  useEffect(() => {
  const token = localStorage.getItem("authToken");
  if (token) {
    getCurrentUser({ token })
      .then(res => {
        if (res?.user) {
          setEmailVerified(res.user.email_verified_at !== null);
        }
      })
      .catch(err => {
        console.error("Fetch user error", err);
        setEmailVerified(true);
      });
  }
}, []);


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
          variant="temporary"
          open={mobileOpen}
          onClose={handleDrawerToggle}
          ModalProps={{ keepMounted: true }}
          sx={{
            "& .MuiDrawer-paper": { width: 240, backgroundColor: "#1e1e1e" },
          }}>
          <Sidebar />
        </Drawer>
      ) : (
        <Box sx={{ width: 240, flexShrink: 0 }}>
          <Sidebar />
        </Box>
      )}

      <Box sx={{ flexGrow: 1, p: 2 }}>
        {!emailVerified && (
        <Box
          sx={{
            backgroundColor: "#ff9800",
            color: "#000",
            borderRadius: 1,
            p: 2,
            mb: 2,
            fontWeight: "bold",
            textAlign: "center",
          }}>
          Please verify your email to unlock all features.
        </Box>
      )}
        <SearchAppBar
          searchValue={searchValue}
          onSearchChange={setSearchValue}
          onSearchSubmit={handleSearch}
          onMenuClick={handleDrawerToggle}
        />

        <Grid container spacing={3} sx={{ mt: 1 }}>
          {/* Weather card */}
          <Grid item xs={12} md={6}>
            <WeatherCard
              city={
                `${airData?.station.city ?? city}` +
                `, ${airData?.station.address}`
              }
              dateStr={todayStr}
              weather={weather}
              loading={weatherLoading}
            />
          </Grid>
          {/* Air pollution chart */}
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
          {/* Map */}
          <Grid item xs={12} md={6}>
          <Card sx={{ height: 400, borderRadius: 2, boxShadow: 3 }}>
            <CityMap coords={getCoords()} city={city} />
          </Card>
          </Grid>
        </Grid>
      </Box>
    </Box>
  );
};

export default Dashboard;
