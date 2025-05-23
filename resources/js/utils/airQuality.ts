export const pollutantThresholds: Record<
  string,
  { good: number; moderate: number; unhealthy: number }
> = {
  pm25: { good: 10, moderate: 25, unhealthy: 50 },
  pm10: { good: 20, moderate: 50, unhealthy: 100 },
  no2: { good: 40, moderate: 100, unhealthy: 200 },
  so2: { good: 50, moderate: 125, unhealthy: 300 },
  o3: { good: 60, moderate: 120, unhealthy: 180 },
  co: { good: 3, moderate: 6, unhealthy: 10 },
};

export function getAirQualityLevel(
  parameter: string,
  rawValue: number | string,
): { level: string; color: string } {
  const value = typeof rawValue === "string" ? parseFloat(rawValue) : rawValue;
  const norm = pollutantThresholds[parameter.toLowerCase()];
  if (!norm) return { level: "Unknown", color: "#999" };
  if (value <= norm.good) return { level: "Good", color: "#00e676" };
  if (value <= norm.moderate) return { level: "Moderate", color: "#ffeb3b" };
  if (value <= norm.unhealthy) return { level: "Unhealthy", color: "#ff9800" };
  return { level: "Very Unhealthy", color: "#f44336" };
}
