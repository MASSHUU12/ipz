import { instance } from "./api";

export interface SynopResponse {
  station_id: string;
  station_name: string;
  measurement_date: string;
  measurement_hour: string;
  temperature: number;
  wind_speed: number;
  wind_direction: number;
  relative_humidity: number;
  rainfall_total: number;
  pressure: number;
}

export const getSynop = async (
  station: string,
): Promise<SynopResponse | null> => {
  try {
    const response = await instance.get<SynopResponse>(
      `/synop?station=${station}&format=json`,
    );
    return response.data;
  } catch (error: unknown) {
    if (error instanceof Error) {
      console.error(error);
    } else {
      console.error("An unknown error occurred.");
    }
    return null;
  }
};
