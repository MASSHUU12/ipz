import React from "react";
import { MapPayload } from "../../api/chatWidgetApi";
import "./MapDisplay.css";

interface MapDisplayProps {
  payload: MapPayload;
}

export const MapDisplay: React.FC<MapDisplayProps> = ({ payload }) => {
  const { lat, lng, label, zoom = 13 } = payload;
  
  // Validate coordinates
  const isValidLat = typeof lat === "number" && lat >= -90 && lat <= 90;
  const isValidLng = typeof lng === "number" && lng >= -180 && lng <= 180;
  
  if (!isValidLat || !isValidLng) {
    return (
      <div className="map-display">
        <div className="map-display__info">
          <strong>Invalid coordinates</strong>
        </div>
      </div>
    );
  }
  
  // Encode coordinates for URL safety
  const encodedLat = encodeURIComponent(lat.toFixed(6));
  const encodedLng = encodeURIComponent(lng.toFixed(6));
  const encodedZoom = encodeURIComponent(zoom);
  
  // OpenStreetMap URL
  const mapUrl = `https://www.openstreetmap.org/?mlat=${encodedLat}&mlon=${encodedLng}#map=${encodedZoom}/${encodedLat}/${encodedLng}`;
  
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
