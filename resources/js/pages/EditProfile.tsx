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
  Snackbar,
  Alert,
} from "@mui/material";
import { router, usePage } from "@inertiajs/react";
import Sidebar from "./Sidebar";
import { RequireAuth } from "./RequireAuth";
import { instance } from "@/api/api";
import { isValidEmail } from "./regex";

interface User {
  id: number;
  email: string | null;
  phone_number: string | null;
}

interface PageProps {
  auth: {
    user: User;
  };
  [key: string]: any;
}

const EditProfile = () => {
  const isMobile = useMediaQuery("(max-width:900px)");
  const [drawerOpen, setDrawerOpen] = useState(false);
  const { props } = usePage<PageProps>();
  const currentEmail = props.auth?.user?.email ?? "";

  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [repeatPassword, setRepeatPassword] = useState("");
  const [newEmail, setNewEmail] = useState("");
  const [confirmNewEmail, setConfirmNewEmail] = useState("");

  const [errors, setErrors] = useState<{
    currentPassword?: string;
    newPassword?: string;
    repeatPassword?: string;
  }>({});

  const [emailErrors, setEmailErrors] = useState<{ newEmail?: string; confirmNewEmail?: string }>({});

  const [snackbar, setSnackbar] = useState<{
    open: boolean;
    message: string;
    severity: "success" | "error";
  }>({
    open: false,
    message: "",
    severity: "success",
  });

  const handleEmailChange = async () => {
    setEmailErrors({});

    if (!newEmail) {
      setEmailErrors({ newEmail: "New email is required." });
      return;
    }

    if (!confirmNewEmail) {
      setEmailErrors({ confirmNewEmail: "Please confirm your new email." });
      return;
    }

    if (newEmail !== confirmNewEmail) {
      setEmailErrors({
        newEmail: "Emails do not match.",
        confirmNewEmail: "Emails do not match.",
      });
      return;
    }

    if (newEmail === currentEmail) {
      setEmailErrors({ newEmail: "New email cannot be the same as the current one." });
      return;
    }

    if (!isValidEmail(newEmail)) {
      setEmailErrors({ newEmail: "Invalid email format." });
      return;
    }

    if (newEmail.length > 255) {
      setEmailErrors({ newEmail: "Email must be less than 255 characters." });
      return;
    }

    try {
      await instance.patch(
        "/user",
        { email: newEmail },
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken") || ""}`,
          },
        }
      );

      localStorage.removeItem("authToken");
      router.visit("/login");
    } catch (error: any) {
      const message = error.response?.data?.message || "Failed to update email.";
      setSnackbar({
        open: true,
        message,
        severity: "error",
      });
    }
  };

  const handleSubmit = async () => {
    setErrors({});
    const newErrors: Record<string, string> = {};

    if (!currentPassword) newErrors.currentPassword = "This field is required.";
    if (!newPassword) newErrors.newPassword = "This field is required.";
    if (!repeatPassword) newErrors.repeatPassword = "This field is required.";

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    if (newPassword === currentPassword) {
      setErrors({
        newPassword: "New password cannot be the same as the current one.",
        currentPassword: "New password cannot be the same as the current one.",
        repeatPassword: "New password cannot be the same as the current one.",
      });
      return;
    }

    if (newPassword !== repeatPassword) {
      setErrors({
        newPassword: "Passwords do not match.",
        repeatPassword: "Passwords do not match.",
      });
      return;
    }

    if (newPassword.length < 8 || newPassword.length > 255) {
      setErrors({
        newPassword: "Password must be between 8 and 255 characters.",
        repeatPassword: "Password must be between 8 and 255 characters.",
      });
      return;
    }

    const hasUpperCase = /[A-Z]/.test(newPassword);
    const hasLowerCase = /[a-z]/.test(newPassword);
    const hasNumber = /[0-9]/.test(newPassword);
    const hasSpecialChar = /[@$!%*?&]/.test(newPassword);

    if (!hasUpperCase || !hasLowerCase || !hasNumber || !hasSpecialChar) {
      setErrors({
        newPassword: "Password must include uppercase, lowercase, number, and special character.",
        repeatPassword: "Password must include uppercase, lowercase, number, and special character.",
      });
      return;
    }

    try {
      await instance.patch(
        "/user/password",
        {
          current_password: currentPassword,
          new_password: newPassword,
          new_password_confirmation: repeatPassword,
        },
        {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        }
      );

      localStorage.removeItem("authToken");
      router.visit("/login");
    } catch (error: any) {
      const backendError = error.response?.data?.message || "Failed to update password.";
      setSnackbar({ open: true, message: backendError, severity: "error" });
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
            open={drawerOpen}
            onClose={() => setDrawerOpen(false)}
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
            overflowX: "hidden",
          }}
        >
          <Box sx={{ width: "100%", maxWidth: "600px" }}>
            <Box sx={{ display: "flex", alignItems: "center", gap: 2, mb: 2 }}>
              {isMobile && (
                <Button onClick={() => setDrawerOpen(true)} sx={{ minWidth: 0, p: 1 }}>
                  <span style={{ fontSize: "1.5rem" }}>☰</span>
                </Button>
              )}
              <Typography variant="h4">Edit Profile</Typography>
            </Box>

            {/* --- Change Email Section --- */}
            <Typography variant="h5" gutterBottom>
              Change Email
            </Typography>

            <Card sx={{ backgroundColor: "#222", borderRadius: 2, p: { xs: 2, sm: 4 }, mb: 4 }}>
              <CardContent>
                <Box sx={{ display: "flex", flexDirection: "column", gap: 3 }}>
                  <TextField
                    label="New Email"
                    fullWidth
                    type="email"
                    value={newEmail}
                    onChange={(e) => setNewEmail(e.target.value)}
                    error={Boolean(emailErrors.newEmail)}
                    helperText={emailErrors.newEmail}
                    variant="outlined"
                    sx={{
                      backgroundColor: "#2e2e2e",
                      input: { color: "#fff" },
                      label: { color: "#bbb" },
                    }}
                  />
                  <TextField
                    label="Confirm New Email"
                    fullWidth
                    type="email"
                    value={confirmNewEmail}
                    onChange={(e) => setConfirmNewEmail(e.target.value)}
                    error={Boolean(emailErrors.confirmNewEmail)}
                    helperText={emailErrors.confirmNewEmail}
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
                      onClick={handleEmailChange}
                      variant="contained"
                      fullWidth
                      sx={{ backgroundColor: "#22c55e", textTransform: "none" }}
                    >
                      Save Changes
                    </Button>
                    <Button
                      onClick={handleCancel}
                      variant="outlined"
                      fullWidth
                      sx={{ color: "#ccc", borderColor: "#555", textTransform: "none" }}
                    >
                      Cancel
                    </Button>
                  </Box>
                </Box>
              </CardContent>
            </Card>

            {/* --- Change Password Section --- */}
            <Typography variant="h5" gutterBottom>
              Change Password
            </Typography>

            <Card sx={{ backgroundColor: "#222", borderRadius: 2, p: { xs: 2, sm: 4 } }}>
              <CardContent>
                <Box sx={{ display: "flex", flexDirection: "column", gap: 3 }}>
                  <TextField
                    label="Current password"
                    fullWidth
                    type="password"
                    value={currentPassword}
                    onChange={(e) => setCurrentPassword(e.target.value)}
                    error={Boolean(errors.currentPassword)}
                    helperText={errors.currentPassword}
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
                    error={Boolean(errors.newPassword)}
                    helperText={errors.newPassword}
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
                      fullWidth
                      sx={{ backgroundColor: "#22c55e", textTransform: "none" }}
                    >
                      Save Changes
                    </Button>
                    <Button
                      onClick={handleCancel}
                      variant="outlined"
                      fullWidth
                      sx={{ color: "#ccc", borderColor: "#555", textTransform: "none" }}
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

      <Snackbar
        open={snackbar.open}
        autoHideDuration={4000}
        onClose={() => setSnackbar({ ...snackbar, open: false })}
        anchorOrigin={{ vertical: "top", horizontal: "center" }}
      >
        <Alert severity={snackbar.severity} variant="filled" sx={{ width: "100%" }}>
          {snackbar.message}
        </Alert>
      </Snackbar>
    </RequireAuth>
  );
};

export default EditProfile;
