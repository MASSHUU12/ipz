import { useMap } from 'react-leaflet';
import { useEffect } from 'react';

const MapUpdater = ({ coords }: { coords: { lat: number; lng: number } }) => {
  const map = useMap();

  useEffect(() => {
    if (coords) {
      map.flyTo(coords, 13);
    }
  }, [coords, map]);

  return null;
};
