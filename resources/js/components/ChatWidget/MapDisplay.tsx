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
            <div className="map-display__static-placeholder">
              <div className="map-display__marker">📍</div>
              <div className="map-display__location-text">{label || "Location"}</div>
            </div>
          </div>
          <span className="map-display__link-text">
            View on OpenStreetMap ↗
          </span>
        </a>
      </div>
    </div>
  );
};
