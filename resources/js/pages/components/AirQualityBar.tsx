import GaugeChart from "react-gauge-chart";
const AirQualityBar = () => {
  return (
    <GaugeChart
      id="gauge-chart1"
      nrOfLevels={5}
      colors={["#00e400", "#a8e400", "#ffd700", "#ff7e00", "#99004c"]}
      arcWidth={0.3}
      percent={0.5} // 0–1
      textColor="#ffffff"
      needleColor="#ffffff"
      needleBaseColor="#cccccc"
    />
  );
};

export default AirQualityBar;