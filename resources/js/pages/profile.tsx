import React from "react";
import {
  Box,
  Typography,
  Card,
  CardContent,
  Avatar,
  TextField,
  IconButton,
  Button,
  InputAdornment,
  useMediaQuery,
  Drawer,
} from "@mui/material";
import { Favorite, LocationCity, Menu as MenuIcon } from "@mui/icons-material";
import Sidebar from "./Sidebar";
import { Link } from "@inertiajs/react";
import { RequireAuth } from "./RequireAuth";

const Profile = () => {
  const [city, setCity] = React.useState("");
  const [drawerOpen, setDrawerOpen] = React.useState(false);
  const isMobile = useMediaQuery("(max-width:900px)");
  const [user, setUser] = React.useState<{ email?: string; phone_number?: string }>({});

  React.useEffect(() => {
    if (typeof window !== "undefined") {
      const stored = localStorage.getItem("user");
      if (stored) {
        try {
          setUser(JSON.parse(stored));
        } catch {
          console.error("Incorrect JSON");
        }
      }
    }
  }, []);

  const handleAddCity = () => {
    if (city.trim()) {
      console.log("Added city:", city);
      setCity("");
    }
  };

  return (
    <RequireAuth>
    <Box sx={{ display: "flex", backgroundColor: "#1e1e1e", minHeight: "100vh", color: "#fff" }}>
      {isMobile ? (
        <Drawer
          variant="temporary"
          open={drawerOpen}
          onClose={() => setDrawerOpen(false)}
          ModalProps={{ keepMounted: true }}
          sx={{ "& .MuiDrawer-paper": { width: 240, backgroundColor: "#1e1e1e" } }}
        >
          <Sidebar />
        </Drawer>
      ) : (
        <Box sx={{ width: 240, flexShrink: 0 }}>
          <Sidebar />
        </Box>
      )}

      <Box sx={{ flexGrow: 1, width: "100%", px: 2, py: 4 }}>
        {isMobile && (
          <Box sx={{ mb: 2 }}>
            <IconButton onClick={() => setDrawerOpen(true)} sx={{ color: "#fff" }}>
              <MenuIcon />
            </IconButton>
          </Box>
        )}

        <Box sx={{ maxWidth: 1000, mx: "auto" }}>
          <Typography variant="h4" gutterBottom>
            My Profile
          </Typography>

          <Card sx={{ backgroundColor: "#222", borderRadius: 2, mb: 4 }}>
            <Box sx={{ height: 160, backgroundColor: "#2c2c2c", borderTopLeftRadius: 8, borderTopRightRadius: 8 }} />

            <CardContent>
              <Box
                sx={{
                  display: "flex",
                  flexDirection: { xs: "column", sm: "row" },
                  alignItems: { xs: "flex-start", sm: "center" },
                  gap: 2,
                  mt: -7,
                }}
              >
                <Avatar
                  src="/avatar.png"
                  sx={{ width: 80, height: 80, border: "3px solid #1e1e1e",background:"lightblue" }}
                />
                <Box>
                  {user.email && (
                    <Typography variant="subtitle1" color="white">
                      {user.email}
                    </Typography>
                  )}
                  {user.phone_number && (
                    <Typography variant="body1" color="white">
                      {user.phone_number}
                    </Typography>
                  )}
                </Box>
                <Box sx={{ flexGrow: 1 }} />
                <Link href="/edit-profile">
                  <Button variant="contained" sx={{ color: "white", borderColor: "#1976d2" }}>
                    Edit Profile
                  </Button>
                </Link>
              </Box>
            </CardContent>
          </Card>

          <Card sx={{ backgroundColor: "#222", borderRadius: 2 }}>
            <CardContent>
              <Typography color="white" variant="h6" gutterBottom sx={{ display: "flex", alignItems: "center" }}>
                <Favorite sx={{ mr: 1 }} />
                Favourite Cities
              </Typography>

              <Box sx={{ display: "flex", mt: 1, flexDirection: { xs: "column", sm: "row" }, gap: 2 }}>
                <TextField
                  fullWidth
                  variant="outlined"
                  size="small"
                  placeholder="Add city..."
                  value={city}
                  onChange={(e) => setCity(e.target.value)}
                  sx={{
                    backgroundColor: "#2e2e2e",
                    input: { color: "#fff" },
                    borderRadius: 1,
                  }}
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start"> 
                        <LocationCity sx={{ color: "#90caf9" }} />
                      </InputAdornment>
                    ),
                  }}
                />
                <Button variant="contained" onClick={handleAddCity} sx={{ backgroundColor: "#1976d2" }}>
                  Add
                </Button>
              </Box>
            </CardContent>
          </Card>
        </Box>
      </Box>
    </Box>
    </RequireAuth>
  );
};

export default Profile;
