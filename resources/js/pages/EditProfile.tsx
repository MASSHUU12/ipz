import React, { useState } from "react";
import {
  Box,
  Typography,
  Card,
  CardContent,
  TextField,
  Button,
  useMediaQuery,
  Drawer,
} from "@mui/material";
import { router } from "@inertiajs/react";
import Sidebar from "./Sidebar";
import { RequireAuth } from "./RequireAuth";

const EditProfile = () => {
  const isMobile = useMediaQuery("(max-width:900px)");
  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [repeatPassword, setRepeatPassword] = useState("");
  const [errors, setErrors] = useState<{ repeatPassword?: string }>({});

  const handleSubmit = () => {
    setErrors({});

    if (!currentPassword || !newPassword || !repeatPassword) {
        setErrors({ repeatPassword: "All fields are required." });
        return;
      }
      if (newPassword == currentPassword) {
        setErrors({ repeatPassword: "New password cannot be the same as old password" });
        return;
      }
      if (newPassword !== repeatPassword) {
        setErrors({ repeatPassword: "Passwords do not match." });
        return;
      }

      if (newPassword.length < 8) {
        setErrors({ repeatPassword: "Password must have at least 8 characters." });
        return;
      }
      
      if (newPassword.length > 255) {
        setErrors({ repeatPassword: "Password must be less than 255 characters." });
        return;
      }
      
      const hasUpperCase = /[A-Z]/.test(newPassword);
      const hasLowerCase = /[a-z]/.test(newPassword);
      const hasNumber = /[0-9]/.test(newPassword);
      const hasSpecialChar = /[@$!%*?&]/.test(newPassword);
      
      if (!hasUpperCase || !hasLowerCase || !hasNumber || !hasSpecialChar) {
        setErrors({
          repeatPassword: "Password must include uppercase, lowercase, number and special character.",
        });
        return;
      }
  };

  const handleCancel = () => {
    router.visit("/profile");
  };

  return (
    <RequireAuth>
    <Box sx={{ display: "flex", backgroundColor: "#1e1e1e", minHeight: "100vh", color: "#fff" }}>
        
      {isMobile ? (
        <Drawer
          variant="temporary"
          open={false}
          onClose={() => {}}
          ModalProps={{ keepMounted: true }}
        >
          <Sidebar />
        </Drawer>
      ) : (
        <Box sx={{ width: 240, minWidth: 200, flexShrink: 0 }}>
          <Sidebar />
        </Box>
      )}

      <Box
        sx={{
          flexGrow: 1,
          p: { xs: 2, sm: 4 },
          display: "flex",
          justifyContent: "center",
          alignItems: "flex-start",
        }}
      >
        <Box sx={{ width: "100%", maxWidth: "1000px" }}>
          <Typography variant="h4" gutterBottom>
            Edit Profile
          </Typography>

          <Card sx={{ backgroundColor: "#222", borderRadius: 2, p: { xs: 2, sm: 4 }, width: "100%" }}>
            <CardContent>
              <Box sx={{ display: "flex", flexDirection: "column", gap: 3 }}>
                <TextField
                  label="Current password"
                  fullWidth
                  type="password"
                  value={currentPassword}
                  onChange={(e) => setCurrentPassword(e.target.value)}
                  variant="outlined"
                  sx={{
                    backgroundColor: "#2e2e2e",
                    input: { color: "#fff" },
                    label: { color: "#bbb" },
                  }}
                />

                <TextField
                  label="New password"
                  fullWidth
                  type="password"
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  variant="outlined"
                  sx={{
                    backgroundColor: "#2e2e2e",
                    input: { color: "#fff" },
                    label: { color: "#bbb" },
                  }}
                />

                <TextField
                  label="Repeat new password"
                  fullWidth
                  type="password"
                  value={repeatPassword}
                  onChange={(e) => setRepeatPassword(e.target.value)}
                  error={Boolean(errors.repeatPassword)}
                  helperText={errors.repeatPassword}
                  variant="outlined"
                  sx={{
                    backgroundColor: "#2e2e2e",
                    input: { color: "#fff" },
                    label: { color: "#bbb" },
                  }}
                />

                <Box
                  sx={{
                    display: "flex",
                    flexDirection: { xs: "column", sm: "row" },
                    gap: 2,
                    mt: 2,
                  }}
                >
                  <Button
                    onClick={handleSubmit}
                    variant="contained"
                    sx={{ backgroundColor: "#22c55e", flex: 1, textTransform: "none" }}
                  >
                    Save Changes
                  </Button>
                  <Button
                    onClick={handleCancel}
                    variant="outlined"
                    sx={{ color: "#ccc", borderColor: "#555", flex: 1, textTransform: "none" }}
                  >
                    Cancel
                  </Button>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Box>
      </Box>
    </Box>
    </RequireAuth>
  );
};

export default EditProfile;
