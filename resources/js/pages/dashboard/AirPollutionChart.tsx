import { MeasurementRecord, Measurements } from "@/api/airQualityApi";
import { getAirQualityLevel } from "@/utils/airQuality";
import { Typography } from "@mui/material";
import React, { useMemo } from "react";
import {
  ResponsiveContainer,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  CartesianGrid,
  Cell,
} from "recharts";

interface Props {
  measurements: Measurements | undefined;
}

interface ChartEntry {
  name: string;
  value: number;
  level: string;
  color: string;
}

export const AirPollutionChart: React.FC<Props> = ({ measurements }) => {
  const data: ChartEntry[] = useMemo(() => {
    if (!measurements || measurements.length === 0) {
      return [];
    }

    const groups = measurements.reduce<Record<string, MeasurementRecord[]>>(
      (acc, record) => {
        const key = record.code.toLowerCase();
        if (!acc[key]) {
          acc[key] = [];
        }
        acc[key].push(record);
        return acc;
      },
      {},
    );

    return Object.entries(groups)
      .map(([code, records]) => {
        const latest = records.reduce((a, b) =>
          new Date(a.measurementTime) > new Date(b.measurementTime) ? a : b,
        );

        if (latest.value === null) {
          return null;
        }

        const { level, color } = getAirQualityLevel(code, latest.value);

        return {
          name: code.toUpperCase(),
          value: latest.value,
          level,
          color,
        } as ChartEntry;
      })
      .filter((entry): entry is ChartEntry => entry !== null);
  }, [measurements]);

  if (!data.length) {
    return (
      <Typography variant="subtitle1">
        Air pollution data is unavailable for this location.
      </Typography>
    );
  }

  return (
    <ResponsiveContainer width="100%" height={250}>
      <BarChart data={data} margin={{ top: 20, right: 30, left: 0, bottom: 5 }}>
        <CartesianGrid strokeDasharray="3 3" stroke="#444" />
        <XAxis dataKey="name" stroke="#ccc" tick={{ fontSize: 12 }} />
        <YAxis stroke="#ccc" />
        <Tooltip
          formatter={(value: number, _name: string, props) => {
            return [`${value} µg/m³ – ${props.payload.level}`];
          }}
          contentStyle={{ backgroundColor: "#333", borderRadius: 8 }}
          labelStyle={{ color: "#fff" }}
          itemStyle={{ color: "#fff" }}
        />
        <Bar
          dataKey="value"
          radius={[8, 8, 0, 0]}
          label={{ position: "top", fill: "#fff" }}>
          {data.map((entry, i) => (
            <Cell key={i} fill={entry.color} />
          ))}
        </Bar>
      </BarChart>
    </ResponsiveContainer>
  );
};
