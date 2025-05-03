import { instance } from "./api";

export interface SuggestAddressesResponse {
  suggestions: string[];
}

export const suggestAddresses = async (
  q: string,
): Promise<SuggestAddressesResponse | null> => {
  try {
    const res = await instance.get<SuggestAddressesResponse>(
      "/addresses/suggest",
      {
        params: { q },
      },
    );
    console.log(res.data);

    return res.data;
  } catch (error: unknown) {
    if (error instanceof Error) console.error(error.message);
    else console.error("Unknown error fetching address suggestions");
    return null;
  }
};
