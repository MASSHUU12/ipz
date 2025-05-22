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
  Button,
} from "@mui/material";
import Sidebar from "./Sidebar";
import { fetchLeaderboard, LeaderboardEntry } from "@/api/leaderboardApi";

type Order = "asc" | "desc";

const columns = [
  { id: "aqiRank", label: "AQI Rank" },
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
  const [rawData, setRawData] = useState<LeaderboardEntry[]>([]);
  const [data, setData] = useState<LeaderboardEntry[]>([]);
  const [loading, setLoading] = useState(true);
  const [orderBy, setOrderBy] = useState<keyof LeaderboardEntry>("air_quality_index");
  const [order, setOrder] = useState<Order>("desc");
  const [currentPage, setCurrentPage] = useState(1);
  const [totalItems, setTotalItems] = useState(0);

  const itemsPerPage = 10;
  const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

  // Pobieranie danych tylko przy zmianie strony
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const { entries, total } = await fetchLeaderboard(currentPage, itemsPerPage);

        const withAqiRank = [...entries]
          .sort((a, b) => (b.air_quality_index ?? -Infinity) - (a.air_quality_index ?? -Infinity))
          .map((entry, index) => ({
            ...entry,
            aqiRank: index + 1,
          }));

        setRawData(withAqiRank);
        setTotalItems(total);
      } catch (err) {
        console.error("Failed to load leaderboard:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [currentPage]);

  // Sortowanie lokalne przy zmianie kolumny
  useEffect(() => {
    const sorted = [...rawData].sort((a, b) => {
      const valA = typeof a[orderBy] === "number" ? a[orderBy] as number : -Infinity;
      const valB = typeof b[orderBy] === "number" ? b[orderBy] as number : -Infinity;
      return order === "asc" ? valA - valB : valB - valA;
    });

    setData(sorted);
  }, [orderBy, order, rawData]);

  const handleSort = (property: keyof LeaderboardEntry) => {
    const isAsc = orderBy === property && order === "asc";
    setOrder(isAsc ? "desc" : "asc");
    setOrderBy(property);
  };

  const handleNextPage = () => {
    if (currentPage < totalPages) setCurrentPage((prev) => prev + 1);
  };

  const handlePrevPage = () => {
    if (currentPage > 1) setCurrentPage((prev) => prev - 1);
  };

  const getColorForValue = (param: keyof LeaderboardEntry, value: number | null): string => {
    if (value === null) return "#fff";
    switch (param) {
      case "pm10": return value <= 50 ? "#22c55e" : value <= 100 ? "#eab308" : "#ef4444";
      case "pm25": return value <= 25 ? "#22c55e" : value <= 50 ? "#eab308" : "#ef4444";
      case "no2": return value <= 200 ? "#22c55e" : value <= 400 ? "#eab308" : "#ef4444";
      case "so2": return value <= 20 ? "#22c55e" : value <= 50 ? "#eab308" : "#ef4444";
      case "o3": return value <= 100 ? "#22c55e" : value <= 180 ? "#eab308" : "#ef4444";
      case "co": return value <= 4 ? "#22c55e" : value <= 10 ? "#eab308" : "#ef4444";
      case "air_quality_index": return value <= 50 ? "#22c55e" : value <= 100 ? "#eab308" : "#ef4444";
      default: return "#fff";
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
        <Card sx={{ backgroundColor: "#1e1e1e", color: "#fff", p: 4, boxShadow: "0 0 15px rgba(0,0,0,0.5)", borderRadius: "12px" }}>
          <Typography variant="h5" gutterBottom>
            Air Quality Ranking
          </Typography>

          {loading ? (
            <CircularProgress color="inherit" />
          ) : (
            <>
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
                    {data.map((row, idx) => (
                      <TableRow key={idx} hover sx={{ backgroundColor: "#1e1e1e", "&:hover": { backgroundColor: "#2e2e2e" }, transition: "background-color 0.2s ease" }}>
                        <TableCell sx={{ color: "#fff" }}>{row.aqiRank}</TableCell>
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

              <Box sx={{ display: "flex", justifyContent: "center", mt: 3, gap: 2 }}>
                <Button variant="contained" onClick={handlePrevPage} disabled={currentPage === 1} sx={{ backgroundColor: "#1976d2", color: "#000", fontWeight: "bold" }}>
                  Previous
                </Button>
                <Typography sx={{ alignSelf: "center" }}>
                  Page {currentPage} of {totalPages}
                </Typography>
                <Button variant="contained" onClick={handleNextPage} disabled={currentPage === totalPages} sx={{ backgroundColor: "#1976d2", color: "#000", fontWeight: "bold" }}>
                  Next
                </Button>
              </Box>
            </>
          )}
        </Card>
      </Box>
    </Box>
  );
}
