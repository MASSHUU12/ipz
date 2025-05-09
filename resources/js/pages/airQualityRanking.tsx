import React, { useEffect, useState } from "react";
import {
  Box,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TableSortLabel,
  Typography,
  CircularProgress,
  Paper,
  Card,
} from "@mui/material";
import { instance } from "@/api/api";
import Sidebar from "./Sidebar";

interface LeaderboardEntry {
  city: string;
  air_quality_index: number | null;
  pm10: number | null;
  pm25: number | null;
  no2: number | null;
  so2: number | null;
  o3: number | null;
  co: number | null;
}

type Order = "asc" | "desc";

const columns = [
  { id: "rank", label: "Rank" },
  { id: "city", label: "City" },
  { id: "air_quality_index", label: "AQI rate" },
  { id: "pm10", label: "PM10" },
  { id: "pm25", label: "PM2.5" },
  { id: "no2", label: "NO₂" },
  { id: "so2", label: "SO₂" },
  { id: "o3", label: "O₃" },
  { id: "co", label: "CO" },
];

export default function AirQualityRanking(): JSX.Element {
  const [data, setData] = useState<LeaderboardEntry[]>([]);
  const [loading, setLoading] = useState(true);
  const [orderBy, setOrderBy] = useState<keyof LeaderboardEntry>("air_quality_index");
  const [order, setOrder] = useState<Order>("desc");

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await instance.get("/leaderboard");
        const raw = res.data?.data;
        if (Array.isArray(raw)) {
          setData(
            raw.map((entry) => ({
              city: entry.city ?? "Nieznane",
              air_quality_index: Number(entry.air_quality_index) ?? null,
              pm10: Number(entry.pm10) ?? null,
              pm25: Number(entry.pm25) ?? null,
              no2: Number(entry.no2) ?? null,
              so2: Number(entry.so2) ?? null,
              o3: Number(entry.o3) ?? null,
              co: Number(entry.co) ?? null,
            }))
          );
        } else {
          setData([]);
        }
      } catch (err) {
        console.error("Failed to load leaderboard:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
    const interval = setInterval(fetchData, 300000);
    return () => clearInterval(interval);
  }, []);

  const handleSort = (property: keyof LeaderboardEntry) => {
    const isAsc = orderBy === property && order === "asc";
    setOrder(isAsc ? "desc" : "asc");
    setOrderBy(property);
  };


  const sortedData = [...data].sort((a, b) => {
    const valA = (a[orderBy] ?? -Infinity) as number;
    const valB = (b[orderBy] ?? -Infinity) as number;
    return order === "asc" ? valA - valB : valB - valA;
  });

  const getColorForValue = (param: keyof LeaderboardEntry, value: number | null): string => {
    if (value === null) return "#fff";
    switch (param) {
      case "pm10":
        return value <= 50 ? "#22c55e" : value <= 100 ? "#eab308" : "#ef4444";
      case "pm25":
        return value <= 25 ? "#22c55e" : value <= 50 ? "#eab308" : "#ef4444";
      case "no2":
        return value <= 200 ? "#22c55e" : value <= 400 ? "#eab308" : "#ef4444";
      case "so2":
        return value <= 20 ? "#22c55e" : value <= 50 ? "#eab308" : "#ef4444";
      case "o3":
        return value <= 100 ? "#22c55e" : value <= 180 ? "#eab308" : "#ef4444";
      case "co":
        return value <= 4 ? "#22c55e" : value <= 10 ? "#eab308" : "#ef4444";
      case "air_quality_index":
        return value <= 50 ? "#22c55e" : value <= 100 ? "#eab308" : "#ef4444";
      default:
        return "#fff";
    }
  };

  const formatValue = (value: number | null, decimals = 1): string => {
    return value !== null && value !== undefined ? value.toFixed(decimals) : "–";
  };

  return (
    <Box sx={{ display: "flex", minHeight: "100vh", backgroundColor: "#1e1e1e", color: "#fff" }}>
      <Box sx={{ width: 240 }}>
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
          }}
        >
          <Typography variant="h5" gutterBottom>
            Air Quality Ranking
          </Typography>

          {loading ? (
            <CircularProgress color="inherit" />
          ) : (
            <TableContainer component={Paper} sx={{ backgroundColor: "#2a2a2a", borderRadius: 2 }}>
              <Table>
                <TableHead>
                  <TableRow>
                    {columns.map((col) => (
                      <TableCell
                        key={col.id}
                        sortDirection={orderBy === col.id ? order : false}
                        sx={{ color: "#fff", fontWeight: "bold", backgroundColor: "#333" }}
                      >
                        <TableSortLabel
                          active={orderBy === col.id}
                          direction={orderBy === col.id ? order : "asc"}
                          onClick={() => handleSort(col.id as keyof LeaderboardEntry)}
                          sx={{ color: "#fff", "&.Mui-active": { color: "#00c8ff" } }}
                        >
                          {col.label}
                        </TableSortLabel>
                      </TableCell>
                    ))}
                  </TableRow>
                </TableHead>
                <TableBody>
                  {sortedData.map((row, idx) => (
                    <TableRow key={idx} hover sx={{ backgroundColor: "#1e1e1e", "&:hover": { backgroundColor: "#2e2e2e" }, transition: "background-color 0.2s ease" }}>
                      <TableCell sx={{ color: "#fff" }}>{idx + 1}</TableCell>
                      <TableCell sx={{ color: "#fff" }}>{row.city}</TableCell>
                      <TableCell sx={{ color: getColorForValue("air_quality_index", row.air_quality_index), fontWeight: 600 }}>{formatValue(row.air_quality_index)}</TableCell>
                      <TableCell sx={{ color: getColorForValue("pm10", row.pm10) }}>{formatValue(row.pm10, 0)}</TableCell>
                      <TableCell sx={{ color: getColorForValue("pm25", row.pm25) }}>{formatValue(row.pm25, 0)}</TableCell>
                      <TableCell sx={{ color: getColorForValue("no2", row.no2) }}>{formatValue(row.no2, 0)}</TableCell>
                      <TableCell sx={{ color: getColorForValue("so2", row.so2) }}>{formatValue(row.so2, 0)}</TableCell>
                      <TableCell sx={{ color: getColorForValue("o3", row.o3) }}>{formatValue(row.o3)}</TableCell>
                      <TableCell sx={{ color: getColorForValue("co", row.co) }}>{formatValue(row.co)}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          )}
        </Card>
      </Box>
    </Box>
  );
}
