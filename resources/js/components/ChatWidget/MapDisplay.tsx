import React from "react";
import { MapPayload } from "../../api/chatWidgetApi";
import "./MapDisplay.css";

interface MapDisplayProps {
  payload: MapPayload;
}

export const MapDisplay: React.FC<MapDisplayProps> = ({ payload }) => {
  const { lat, lng, label, zoom = 13 } = payload;
  
  // OpenStreetMap static image URL
  const mapUrl = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=${zoom}/${lat}/${lng}`;
  
  // Create a static map-like preview using OpenStreetMap tile
  const tileZoom = zoom;
  const tileX = Math.floor((lng + 180) / 360 * Math.pow(2, tileZoom));
  const tileY = Math.floor((1 - Math.log(Math.tan(lat * Math.PI / 180) + 1 / Math.cos(lat * Math.PI / 180)) / Math.PI) / 2 * Math.pow(2, tileZoom));
  
  return (
    <div className="map-display">
      <div className="map-display__info">
        <strong>{label || "Location"}</strong>
        <span className="map-display__coords">
          {lat.toFixed(4)}, {lng.toFixed(4)}
        </span>
      </div>
      <div className="map-display__preview">
        <a 
          href={mapUrl} 
          target="_blank" 
          rel="noopener noreferrer"
          className="map-display__link"
        >
          <div className="map-display__thumbnail">
            <img 
              src={`https://tile.openstreetmap.org/${tileZoom}/${tileX}/${tileY}.png`}
              alt={`Map of ${label || 'location'}`}
              className="map-display__tile"
            />
            <div className="map-display__marker">📍</div>
          </div>
          <span className="map-display__link-text">
            View on OpenStreetMap ↗
          </span>
        </a>
      </div>
    </div>
  );
};
