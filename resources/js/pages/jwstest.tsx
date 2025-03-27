import { JSX, useEffect, useState } from "react";

type KeyPair = {
  publicKey: JsonWebKey;
  privateKey: JsonWebKey;
};

async function generateKeyPair(): Promise<CryptoKeyPair> {
  const keyPair: CryptoKeyPair = await window.crypto.subtle.generateKey(
    {
      name: "RSASSA-PKCS1-v1_5",
      modulusLength: 2048,
      publicExponent: new Uint8Array([0x01, 0x00, 0x01]),
      hash: "SHA-256",
    },
    true, // Whether the key is extractable (i.e., can be used in exportKey)
    ["sign", "verify"],
  );
  return keyPair;
}

async function exportKeys(keyPair: CryptoKeyPair): Promise<KeyPair> {
  const publicKey = await window.crypto.subtle.exportKey(
    "jwk",
    keyPair.publicKey,
  );
  const privateKey = await window.crypto.subtle.exportKey(
    "jwk",
    keyPair.privateKey,
  );
  return { publicKey, privateKey };
}

function storeKeys(publicKey: JsonWebKey, privateKey: JsonWebKey): void {
  // TODO: Use IndexedDB for more secure storage
  localStorage.setItem("publicKey", JSON.stringify(publicKey));
  localStorage.setItem("privateKey", JSON.stringify(privateKey));
}

function retrieveKeys(): KeyPair {
  const publicKey = localStorage.getItem("publicKey");
  const privateKey = localStorage.getItem("privateKey");
  return {
    publicKey: publicKey ? JSON.parse(publicKey) : null,
    privateKey: privateKey ? JSON.parse(privateKey) : null,
  };
}

async function importPrivateKey(jwk: JsonWebKey): Promise<CryptoKey> {
  return await window.crypto.subtle.importKey(
    "jwk",
    jwk,
    { name: "RSASSA-PKCS1-v1_5", hash: "SHA-256" },
    false,
    ["sign"],
  );
}

async function importPublicKey(jwk: JsonWebKey): Promise<CryptoKey> {
  return await window.crypto.subtle.importKey(
    "jwk",
    jwk,
    { name: "RSASSA-PKCS1-v1_5", hash: "SHA-256" },
    false,
    ["verify"],
  );
}

async function fetchPublicKey(): Promise<JsonWebKey> {
  const response = await fetch("http://127.0.0.1:8000/api/public-key");
  const publicKey = await response.json();
  return publicKey.data;
}

function base64UrlEncode(arrayBuffer: ArrayBuffer): string {
  return btoa(String.fromCharCode(...new Uint8Array(arrayBuffer)))
    .replace(/\+/g, "-")
    .replace(/\//g, "_")
    .replace(/=+$/, "");
}

export default function JwsTest(): JSX.Element {
  const [keys, setKeys] = useState<KeyPair>(retrieveKeys());
  const [serverPublicKey, setServerPublicKey] = useState<JsonWebKey | null>(
    null,
  );
  const [response, setResponse] = useState("");
  const authToken = "1|ytJmvveEiIIqOUlj1TBYd8LRbqSqfqNIdNLzkJ2m35fa99bc";

  useEffect(() => {
    if (!keys.publicKey || !keys.privateKey) {
      generateKeyPair().then((keyPair) => {
        exportKeys(keyPair).then(({ publicKey, privateKey }) => {
          storeKeys(publicKey, privateKey);
          setKeys({ publicKey, privateKey });
        });
      });
    }

    fetchPublicKey().then(setServerPublicKey);
  }, [keys]);

  async function signPayload(payload: object): Promise<string> {
    if (!serverPublicKey) {
      throw new Error("Server public key not available");
    }

    const encoder = new TextEncoder();
    const data = encoder.encode(JSON.stringify(payload));
    const privateKey = await importPrivateKey(keys.privateKey);
    const payloadHashBuffer = await window.crypto.subtle.digest(
      "SHA-256",
      data,
    );
    const payloadHash = base64UrlEncode(payloadHashBuffer);

    const header = {
      alg: "RS256",
      typ: "JWT",
      kid: "dunno",
      payload_hash: payloadHash,
    };
    const encodedHeader = base64UrlEncode(
      new TextEncoder().encode(JSON.stringify(header)),
    );
    const encodedPayload = base64UrlEncode(data);
    const toSign = `${encodedHeader}.${encodedPayload}`;
    const toSignBuffer = new Uint8Array([...new TextEncoder().encode(toSign)]);
    const signature = await window.crypto.subtle.sign(
      { name: "RSASSA-PKCS1-v1_5" },
      privateKey,
      toSignBuffer,
    );

    const encodedSignature = base64UrlEncode(signature);

    return `${toSign}.${encodedSignature}`;
  }

  async function sendRequest(
    url: string,
    method: string,
    data: object,
  ): Promise<unknown> {
    const jwsToken = await signPayload(data);
    const response = await fetch(url, {
      method: method,
      headers: {
        Authorization: `Bearer ${authToken}`,
        "Content-Type": "application/json",
        "X-JWS-Signature": jwsToken,
      },
      body: JSON.stringify(data),
    });

    console.log("Raw server response:", response);

    return await response.json();
  }

  const handleSend = async (): Promise<void> => {
    const url = "http://127.0.0.1:8000/api/jwstest";
    const method = "POST";
    const responseData = await sendRequest(url, method, {
      data: ":)",
    });
    setResponse(JSON.stringify(responseData));
  };

  return (
    <div>
      <p>JWS Test</p>
      <button onClick={handleSend} disabled={!serverPublicKey}>
        Send
      </button>
      {response && (
        <div>
          <h3>Response:</h3>
          <pre>{response}</pre>
        </div>
      )}
    </div>
  );
}
