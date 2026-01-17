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
  
  // Format coordinates with consistent precision
  const displayLat = lat.toFixed(4);
  const displayLng = lng.toFixed(4);
  
  // Encode coordinates for URL safety
  const encodedLat = encodeURIComponent(lat);
  const encodedLng = encodeURIComponent(lng);
  const encodedZoom = encodeURIComponent(zoom);
  
  // OpenStreetMap URL
  const mapUrl = `https://www.openstreetmap.org/?mlat=${encodedLat}&mlon=${encodedLng}#map=${encodedZoom}/${encodedLat}/${encodedLng}`;
  
  return (
    <div className="map-display">
      <div className="map-display__info">
        <strong>{label || "Location"}</strong>
        <span className="map-display__coords">
          {displayLat}, {displayLng}
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
