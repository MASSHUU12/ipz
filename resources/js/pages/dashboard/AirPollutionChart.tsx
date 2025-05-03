import { Measurement } from "@/api/airQualityApi";
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
  measurements?: Measurement[];
}

export const AirPollutionChart: React.FC<Props> = ({ measurements }) => {
  const data = useMemo(() => {
    if (!measurements) return [];

    return measurements.map((m: Measurement) => {
      const param = normalizeParameter(m.parameter);
      const { level, color } = getAirQualityLevel(param, m.value);
      return {
        name: param.toUpperCase(),
        value: +m.value,
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
