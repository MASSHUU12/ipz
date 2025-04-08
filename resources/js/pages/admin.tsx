import { getCurrentUser } from "@/api/userApi";
import { Box, Button, ButtonGroup, Stack, TextField } from "@mui/material";
import { useEffect, useState } from "react";

export default function Admin(): JSX.Element {
  const [token, setToken] = useState<string>("");
  const [output, setOutput] = useState<string>("");

  useEffect(() => {
    document.title = "IPZ - Overlord's Dashboard";
  }, []);

  async function fetchUserInfo(): Promise<void> {
    const response = await getCurrentUser(token);
    setOutput(JSON.stringify(response, null, 2));
  }

  const buttons = [
    <Button key="user-info" onClick={fetchUserInfo}>
      Fetch my info
    </Button>,
    <Button
      key="surprise"
      onClick={() => {
        window.location.href = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
      }}
    >
      c:
    </Button>,
    <Button>Add buttons to do smth</Button>,
  ];

  return (
    <Stack
      sx={{
        height: "100vh",
        width: "100%",
        "& > *": { m: 1 },
      }}
    >
      <h1>Overlord's Dashboard</h1>
      <h2>THIS PAGE IS NOT SECURED BECAUSE I'M LAZY AND DON'T WANT TO DO IT</h2>
      <TextField
        label="Overlord's Token"
        value={token}
        onChange={(e) => setToken(e.target.value)}
        sx={{
          backgroundColor: "#3a3a3a",
          input: { color: "#fff" },
          label: { color: "#bbb" },
        }}
      />
      <Box
        sx={{
          height: "100%",
          display: "flex",
          flexDirection: "row",
        }}
      >
        <Stack direction="row" spacing={2} sx={{ flex: 1, overflow: "hidden" }}>
          {/* Left-side buttons */}
          <Stack
            direction="column"
            sx={{
              flexShrink: 0,
            }}
          >
            <ButtonGroup orientation="vertical" aria-label="Overlord's Buttons">
              {buttons}
            </ButtonGroup>
          </Stack>

          {/* Right-side output area */}
          <Stack
            direction="column"
            sx={{
              flexGrow: 1,
              display: "flex",
              overflow: "hidden",
            }}
          >
            <TextField
              label="Output"
              fullWidth
              multiline
              InputProps={{
                readOnly: true,
                style: {
                  height: "100%",
                  overflow: "auto",
                  display: "flex",
                  alignItems: "flex-start",
                },
              }}
              value={output}
              sx={{
                backgroundColor: "#3a3a3a",
                "& .MuiInputBase-input": {
                  color: "#fff",
                },
                input: { color: "#fff" },
                label: { color: "#bbb" },
                flex: 1,
              }}
            />
          </Stack>
        </Stack>
      </Box>
    </Stack>
  );
}
