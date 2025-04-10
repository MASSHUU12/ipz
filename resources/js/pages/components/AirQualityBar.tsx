import GaugeChart from "react-gauge-chart";

type Props = {
  index: string;
};

const indexToPercent = (index: string): number => {
  switch (index) {
    case "Bardzo dobry": return 0.0;
    case "Dobry": return 0.2;
    case "Umiarkowany": return 0.4;
    case "Dostateczny": return 0.6;
    case "Zły": return 0.8;
    case "Bardzo zły": return 1.0;
    default: return 0.5;
  }
};

const AirQualityBar = ({ index }: Props) => {
  return (
    <>
      <GaugeChart
        id="gauge-chart1"
        nrOfLevels={5}
        colors={["#00e400", "#a8e400", "#ffd700", "#ff7e00", "#99004c"]}
        arcWidth={0.3}
        percent={indexToPercent(index)}
        textColor="#ffffff"
        needleColor="#ffffff"
        needleBaseColor="#cccccc"
      />
      <p style={{ textAlign: "center", marginTop: 10 }}>{index}</p>
    </>
  );
};

export default AirQualityBar;
