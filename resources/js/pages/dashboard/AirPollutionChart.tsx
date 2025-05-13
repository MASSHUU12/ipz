import { MeasurementRecord } from "@/api/airQualityApi";
import { getAirQualityLevel, normalizeParameter } from "@/utils/airQuality";
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
  measurements?: Record<string, MeasurementRecord[]>;
}

export const AirPollutionChart: React.FC<Props> = ({ measurements }) => {
  const data = useMemo(() => {
    if (!measurements) return [];

    return Object.entries(measurements).map(([pollutant, records]) => {
      const latestRecord = records[0];
      const normalizedParam = normalizeParameter(pollutant);
      const { level, color } = getAirQualityLevel(
        normalizedParam,
        latestRecord.value,
      );
      return {
        name: normalizedParam.toUpperCase(),
        value: latestRecord.value,
        level,
        color,
      };
    });
  }, [measurements]);

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
