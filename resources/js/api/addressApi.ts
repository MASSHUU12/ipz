import { instance, Payload } from "./api";

export interface SuggestAddressesResponse {
    suggestions: string[];
}

export const suggestAddresses = async (
    payload: Payload & { q: string }
): Promise<SuggestAddressesResponse | null> => {
    const { token, q } = payload;
    if (!token) {
        console.error("Token is empty.");
        return null;
    }
    try {
        const res = await instance.get<SuggestAddressesResponse>(
            "/addresses/suggest",
            {
                params: { q },
                headers: { Authorization: `Bearer ${token}` },
            }
        );
        return res.data;
    } catch (error: unknown) {
        if (error instanceof Error) console.error(error.message);
        else console.error("Unknown error fetching address suggestions");
        return null;
    }
};
