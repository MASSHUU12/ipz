
import { instance } from "./api";

export interface LeaderboardEntry {
  city: string;
  air_quality_index: number | null;
  pm10: number | null;
  pm25: number | null;
  no2: number | null;
  so2: number | null;
  o3: number | null;
  co: number | null;
  aqiRank: number;
}

interface LeaderboardResponse {
  data: LeaderboardEntry[];
  total: number; 
}

export const fetchLeaderboard = async (
  page: number,
  per_page: number
): Promise<{ entries: LeaderboardEntry[]; total: number }> => {
  const res = await instance.get("/leaderboard", {
    params: { page, per_page },
  });

  const raw = res.data.data;
  const total = res.data.total;

  const processed = raw.map((entry: any, index: number) => ({
    city: entry.city ?? "Unknown",
    air_quality_index: Number(entry.air_quality_index) ?? null,
    pm10: Number(entry.pm10) ?? null,
    pm25: Number(entry.pm25) ?? null,
    no2: Number(entry.no2) ?? null,
    so2: Number(entry.so2) ?? null,
    o3: Number(entry.o3) ?? null,
    co: Number(entry.co) ?? null,
    aqiRank: entry.aqiRank ?? (page - 1) * per_page + index + 1,
  }));

  return { entries: processed, total };
};


