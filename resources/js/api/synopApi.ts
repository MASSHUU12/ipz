import { instance } from "./api";

export interface SynopResponse {
  id_stacji: string;
  stacja: string;
  data_pomiaru: string;
  godzina_pomiaru: string;
  temperatura: string;
  predkosc_wiatru: string;
  kierunek_wiatru: string;
  wilgotnosc_wzgledna: string;
  suma_opadu: string;
  cisnienie: string;
}

export type SynopDataConverted = Omit<
  SynopResponse,
  | "temperatura"
  | "predkosc_wiatru"
  | "kierunek_wiatru"
  | "wilgotnosc_wzgledna"
  | "suma_opadu"
  | "cisnienie"
> & {
  temperature: number;
  wind_speed: number;
  wind_direction: number;
  relative_humidity: number;
  rainfall_total: number;
  pressure: number;
};

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
