import { MeasurementRecord, Measurements } from "@/api/airQualityApi";
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
  measurements: Measurements;
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
        const key = record.parameter;
        if (!acc[key]) {
          acc[key] = [];
        }
        acc[key].push(record);
        return acc;
      },
      {},
    );

    return Object.entries(groups)
      .map(([parameter, records]) => {
        const latest = records
          .slice()
          .sort(
            (a, b) =>
              new Date(b.measurementTime).getTime() -
              new Date(a.measurementTime).getTime(),
          )[0];

        if (latest.value === null || latest.value === undefined) {
          return null;
        }

        const normalized = normalizeParameter(parameter);
        const { level, color } = getAirQualityLevel(normalized, latest.value);

        return {
          name: normalized.toUpperCase(),
          value: latest.value,
          level,
          color,
        } as ChartEntry;
      })
      .filter((entry): entry is ChartEntry => entry !== null);
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
