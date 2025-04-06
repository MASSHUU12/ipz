import React from "react";
import {
  AppBar,
  Toolbar,
  Typography,
  Container,
  Grid,
  Card,
  CardContent,
  TextField,
  IconButton,
  Avatar,
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
import GaugeChart from "react-gauge-chart"
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
];

const Dashboard = () => {
  return (
    <div style={{ display: "flex", backgroundColor: "#1e1e1e", minHeight: "100vh", color: "#fff" }}>
      <Sidebar />
      <Container sx={{ flexGrow: 1, padding: "20px" }}>
        <AppBar
          position="static"
          sx={{
            marginBottom: "20px",
            backgroundColor: "#1e1e1e",
            color: "#fff",
            padding: "10px",
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
            <IconButton sx={{ color: "#fff", marginLeft: "10px" }}>
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

        {/* Duży kontener zawierający 4 karty */}
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Card
              sx={{
                backgroundColor: "#1e1e1e",
                color: "#fff",
                borderRadius: "10px",
                padding: "20px",
                height: "98%"
              }}
            >
              <Grid container spacing={3}>
                {/* Pogoda */}
                <Grid item xs={12} md={6}>
                  <Card
                    sx={{
                      backgroundColor: "#222",
                      color: "#fff",
                      borderRadius: "10px",
                      padding: "5px",
                      height: "90%",
                    }}
                  >
                    <CardContent>
                      <Typography variant="h6">Mon, 9 Mar, 2025</Typography>
                      <Typography variant="subtitle1">Szczecin</Typography>
                      <div style={{ display: "flex", justifyContent: "space-between", marginTop: "10px" }}>
                        <Typography variant="h4" sx={{ fontWeight: "bold" }}>
                          🌤 12°C
                        </Typography>
                        <Typography variant="body2" sx={{ color: "#aaa" }}>
                          Hourly forecast below
                        </Typography>
                      </div>
                    </CardContent>
                  </Card>
                </Grid>

                {/* Podsumowanie pogody */}
                <Grid item xs={12} md={6}>
                  <Card
                    sx={{
                      backgroundColor: "#222",
                      color: "#fff",
                      borderRadius: "10px",
                      padding: "5px",
                      height: "90%",
                    }}
                  >
                    <CardContent>
                      <Typography variant="h6">Weather Summary</Typography>
                      <ResponsiveContainer width="100%" height={150}>
                        <BarChart data={weatherData}>
                          <XAxis dataKey="day" />
                          <YAxis />
                          <Tooltip />
                          <Bar dataKey="value" fill="#00c8ff" />
                        </BarChart>
                      </ResponsiveContainer>
                    </CardContent>
                  </Card>
                </Grid>

                {/* Jakość powietrza */}
                
                <Grid item xs={12} md={6}>
                  <Card sx={{ backgroundColor: "#222", color: "#fff", borderRadius: "10px", padding: "10px",height: "90%" }}>
                    <CardContent>
                      <Typography variant="h6" gutterBottom>Today's Air Pollution</Typography>
                      <GaugeChart
                        id="air-pollution-gauge"
                        nrOfLevels={5}
                        colors={["#00e400", "#ffff00", "#ff7e00", "#ff0000", "#8f3f97", "#7e0023"]}
                        arcWidth={0.4}
                        percent={0.5} // 0 to 1 scale (np. 0.4 to 40%)
                        textColor="#fff"
                        needleColor="#fff"
                        needleBaseColor="#aaa"
                      />
                    </CardContent>
                  </Card>
                </Grid>

                {/* Wykres powietrza */}
                <Grid item xs={12} md={6}>
                  <Card
                    sx={{
                      backgroundColor: "#222",
                      color: "#fff",
                      borderRadius: "10px",
                      padding: "10px",
                      height: "90%",
                    }}
                  >
                    <CardContent>
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
                </Grid>
              </Grid>
            </Card>
          </Grid>
        </Grid>
      </Container>
    </div>
  );
};

export default Dashboard;
