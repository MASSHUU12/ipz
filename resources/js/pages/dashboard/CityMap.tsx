import React, { useEffect } from "react";
import { MapContainer, TileLayer, Marker, Popup, useMap } from "react-leaflet";
import { LatLngExpression } from "leaflet";

const MapUpdater: React.FC<{ coords: LatLngExpression }> = ({ coords }) => {
  const map = useMap();
  useEffect(() => {
    map.flyTo(coords, 13);
  }, [coords, map]);
  return null;
};

interface Props {
  coords?: LatLngExpression;
  city: string;
}

export const CityMap: React.FC<Props> = ({ coords, city }) => {
  if (!coords) return null;
  return (
    <MapContainer
      center={coords}
      zoom={13}
      style={{ height: "100%", width: "100%" }}>
      <TileLayer
        attribution='&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
      />
      <Marker position={coords}>
        <Popup>{city}</Popup>
      </Marker>
      <MapUpdater coords={coords} />
    </MapContainer>
  );
};
