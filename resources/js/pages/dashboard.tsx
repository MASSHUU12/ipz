import AirQualityBar from "./components/AirQualityBar";
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
  Avatar,
  useMediaQuery,
  Drawer,
  Box,
} from "@mui/material";
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  BarChart,
  Bar,
} from "recharts";
import { Notifications, ArrowDropDown } from "@mui/icons-material";
import Sidebar from "./Sidebar";

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
  const isMobile = useMediaQuery("(max-width:900px)");

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
        <Box sx={{ width: 240, flexShrink: 0 }}>
          <Sidebar />
        </Box>
      )}

      <Box
        sx={{
          flexGrow: 1,
          padding: 2,
          marginLeft: { md: "240px" },
          display: "flex",
          justifyContent: "center",
        }}
      >
        <Box sx={{ maxWidth: {xs:1400}, width: "100%", mx: "auto" }}>
          <AppBar
            position="static"
            sx={{
              marginBottom: 2,
              backgroundColor: "#1e1e1e",
              color: "#fff",
              padding: 1,
            }}
          >
            <Toolbar>
              <TextField
                fullWidth
                placeholder="Search city or postcode"
                variant="outlined"
                size="small"
                sx={{
                  backgroundColor: "#2e2e2e",
                  borderRadius: "10px",
                  input: { color: "#fff" },
                }}
              />
              <IconButton sx={{ color: "#fff", ml: 1 }}>
                <Notifications />
              </IconButton>
              <IconButton sx={{ color: "#fff" }}>
                <Avatar src="/path-to-profile-pic.jpg" />
              </IconButton>
              <IconButton sx={{ color: "#fff" }}>
                <ArrowDropDown />
              </IconButton>
            </Toolbar>
          </AppBar>
          {isMobile ? (
  <>
    {/* Pogoda */}
    <Grid container spacing={3} alignItems="stretch"> 
              {/* Pogoda */}
              <Grid item xs={12} md={6}>
                <Box sx={{ height: "100%" }}>
                  <Card sx={{ height: "90%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: {xs:2,mb:0 }}}>
                    <CardContent sx={{ flexGrow: 1 }}>
                      <Typography variant="h6">Mon, 9 Mar, 2025</Typography>
                      <Typography variant="subtitle1">Szczecin</Typography>
                      <Box display="flex" justifyContent="space-between" mt={2}>
                        <Typography variant="h4" fontWeight="bold">🌤 12°C</Typography>
                        <Typography variant="body2" sx={{ color: "#aaa" }}>Hourly forecast below</Typography>
                      </Box>
                    </CardContent>
                  </Card>
                </Box>
              </Grid>

              {/* Podsumowanie pogody */}
              <Grid item xs={12} md={6}>
                <Box sx={{ height: "100%" }}>
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
                <Box sx={{ height: "100%" }}>
                  <Card sx={{ height: "90%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
                    <CardContent sx={{ flexGrow: 1 }}>
                      <Typography variant="h6" gutterBottom>Today's Air Pollution</Typography>
                      <AirQualityBar />
                    </CardContent>
                  </Card>
                </Box>
              </Grid>

              {/* Wykres powietrza */}
              <Grid item xs={12} md={6}>
                <Box sx={{ height: "100%" }}>
                  <Card sx={{ height: "90%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
                    <CardContent sx={{ flexGrow: 1 }}>
                      <Typography variant="h6">Overview</Typography>
                      <ResponsiveContainer width="100%" height={200}>
                        <LineChart data={airPollutionData}>
                          <CartesianGrid strokeDasharray="3 3" stroke="#444" />
                          <XAxis dataKey="name" stroke="#ccc" />
                          <YAxis stroke="#ccc" />
                          <Tooltip />
                          <Line
                            type="monotone"
                            dataKey="value"
                            stroke="#00c8ff"
                            strokeWidth={2}
                            dot={{ fill: "#00c8ff", r: 4 }}
                          />
                        </LineChart>
                      </ResponsiveContainer>
                    </CardContent>
                  </Card>
                </Box>
              </Grid>
            </Grid>
  </>
) : (
          <Box sx={{ backgroundColor: "#1e1e1e", p:{xs:2,mb:0 },boxShadow:{xs:5,mb:0}, borderRadius: 2 }}>
            <Grid container spacing={3} alignItems="stretch"> 
              {/* Pogoda */}
              <Grid item xs={12} md={6}>
                <Box sx={{ height: "100%" }}>
                  <Card sx={{ height: "80%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: {xs:2,mb:0 }}}>
                    <CardContent sx={{ flexGrow: 1 }}>
                      <Typography variant="h6">Mon, 9 Mar, 2025</Typography>
                      <Typography variant="subtitle1">Szczecin</Typography>
                      <Box display="flex" justifyContent="space-between" mt={2}>
                        <Typography variant="h4" fontWeight="bold">🌤 12°C</Typography>
                        <Typography variant="body2" sx={{ color: "#aaa" }}>Hourly forecast below</Typography>
                      </Box>
                    </CardContent>
                  </Card>
                </Box>
              </Grid>

              {/* Podsumowanie pogody */}
              <Grid item xs={12} md={6}>
                <Box sx={{ height: "100%" }}>
                  <Card sx={{ height: "80%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
                    <CardContent sx={{ flexGrow: 1 }}>
                      <Typography variant="h6">Weather Summary</Typography>
                      <ResponsiveContainer width="100%" height={150}>
                        <BarChart data={weatherData}>
                          <XAxis dataKey="day" />
                          <YAxis />
                          <Tooltip />
                          <Bar dataKey="value" fill="#00c8ff"radius={[10, 10, 0, 0]} />
                        </BarChart>
                      </ResponsiveContainer>
                    </CardContent>
                  </Card>
                </Box>
              </Grid>

              {/* Jakość powietrza */}
              <Grid item xs={12} md={6}>
                <Box sx={{ height: "100%" }}>
                  <Card sx={{ height: "80%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
                    <CardContent sx={{ flexGrow: 1 }}>
                      <Typography variant="h6" gutterBottom>Today's Air Pollution</Typography>
                      <AirQualityBar />
                    </CardContent>
                  </Card>
                </Box>
              </Grid>

              {/* Wykres powietrza */}
              <Grid item xs={12} md={6}>
                <Box sx={{ height: "100%" }}>
                  <Card sx={{ height: "80%", display: "flex", flexDirection: "column", backgroundColor: "#222", color: "#fff", borderRadius: 2, p: 2 }}>
                    <CardContent sx={{ flexGrow: 1 }}>
                      <Typography variant="h6">Overview</Typography>
                      <ResponsiveContainer width="100%" height={200}>
                        <LineChart data={airPollutionData}>
                          <CartesianGrid strokeDasharray="3 3" stroke="#444" />
                          <XAxis dataKey="name" stroke="#ccc" />
                          <YAxis stroke="#ccc" />
                          <Tooltip />
                          <Line
                            type="monotone"
                            dataKey="value"
                            stroke="#00c8ff"
                            strokeWidth={2}
                            dot={{ fill: "#00c8ff", r: 4 }}
                          />
                        </LineChart>
                      </ResponsiveContainer>
                    </CardContent>
                  </Card>
                </Box>
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
