import React, { useState } from "react";
import { createTheme, ThemeProvider } from "@mui/material/styles";
import {
  CssBaseline,
  Box,
  Container,
  Paper,
  Typography,
  TextField,
  Button,
  Grow,
  Tabs,
  Tab,
} from "@mui/material";
import { motion, AnimatePresence } from "framer-motion";

// Ulepszony ciemny motyw Material UI
const theme = createTheme({
  palette: {
    mode: "dark",
    primary: {
      main: "#00bcd4", // Turkus
    },
    secondary: {
      main: "#ff4081", // Róż
    },
    background: {
      default: "#121212",
      paper: "#1e1e1e",
    },
    text: {
      primary: "#ffffff",
    },
  },
  typography: {
    fontFamily: "Roboto, sans-serif",
    h3: {
      fontWeight: 700,
    },
    h6: {
      fontWeight: 400,
    },
  },
});

function Login() {
  const [activeTab, setActiveTab] = useState(0); // 0 = logowanie, 1 = rejestracja
  const [loginData, setLoginData] = useState({ username: "", password: "" });
  const [registerData, setRegisterData] = useState({
    username: "",
    email: "",
    password: "",
    confirmPassword: "",
  });
  const [errors, setErrors] = useState({});

  const handleTabChange = (event, newValue) => {
    setActiveTab(newValue);
    setErrors({});
  };

  // Walidacja formularza logowania
  const validateLogin = () => {
    const newErrors = {};
    if (!loginData.username) newErrors.username = "Wprowadź nazwę użytkownika";
    if (!loginData.password) newErrors.password = "Wprowadź hasło";
    return newErrors;
  };

  // Walidacja formularza rejestracji
  const validateRegister = () => {
    const newErrors = {};
    if (!registerData.username)
      newErrors.username = "Wprowadź nazwę użytkownika";
    if (!registerData.email) newErrors.email = "Wprowadź adres e-mail";
    else if (!/\S+@\S+\.\S+/.test(registerData.email))
      newErrors.email = "Nieprawidłowy adres e-mail";
    if (!registerData.password) newErrors.password = "Wprowadź hasło";
    if (registerData.password && registerData.password.length < 6)
      newErrors.password = "Hasło musi mieć co najmniej 6 znaków";
    if (registerData.password !== registerData.confirmPassword)
      newErrors.confirmPassword = "Hasła nie są zgodne";
    return newErrors;
  };

  const handleLoginSubmit = (e) => {
    e.preventDefault();
    const validationErrors = validateLogin();
    if (Object.keys(validationErrors).length) {
      setErrors(validationErrors);
    } else {
      setErrors({});
      console.log("Login data:", loginData);
      // Dodaj logikę logowania...
    }
  };

  const handleRegisterSubmit = (e) => {
    e.preventDefault();
    const validationErrors = validateRegister();
    if (Object.keys(validationErrors).length) {
      setErrors(validationErrors);
    } else {
      setErrors({});
      console.log("Register data:", registerData);
      // Dodaj logikę rejestracji...
    }
  };

  // Animacje formularzy przy przełączaniu
  const formVariants = {
    initial: { opacity: 0, x: -50 },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: 50 },
  };

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <Container
        maxWidth="sm"
        sx={{
          minHeight: "100vh",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          position: "relative",
        }}
      >
        {/* Pierwszy "rozbłysk" w tle */}
        <Box
          component={motion.div}
          initial={{ scale: 0 }}
          animate={{ scale: 1.3 }}
          transition={{ duration: 2 }}
          sx={{
            position: "absolute",
            top: "-30%",
            left: "-30%",
            width: "300px",
            height: "300px",
            borderRadius: "50%",
            background:
              "radial-gradient(circle, rgba(0,188,212,0.5) 0%, transparent 70%)",
            zIndex: 0,
            filter: "blur(60px)",
          }}
        />

        {/* Drugi "rozbłysk" w tle */}
        <Box
          component={motion.div}
          initial={{ scale: 0 }}
          animate={{ scale: 1.2 }}
          transition={{ duration: 2, delay: 0.5 }}
          sx={{
            position: "absolute",
            bottom: "-20%",
            right: "-20%",
            width: "300px",
            height: "300px",
            borderRadius: "50%",
            background:
              "radial-gradient(circle, rgba(255,64,129,0.5) 0%, transparent 70%)",
            zIndex: 0,
            filter: "blur(60px)",
          }}
        />

        <Grow in timeout={1000}>
          <Paper
            elevation={10}
            sx={{
              p: 4,
              borderRadius: 4,
              position: "relative",
              zIndex: 1,
              overflow: "hidden",
            }}
          >
            {/* Nagłówek */}
            <Box sx={{ textAlign: "center", mb: 2 }}>
              <Typography
                variant="h3"
                gutterBottom
                component={motion.div}
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8 }}
              >
                Witaj w Aplikacji!
              </Typography>
              <Typography
                variant="h6"
                component={motion.div}
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, delay: 0.2 }}
              >
                Zaloguj się lub zarejestruj
              </Typography>
            </Box>

            {/* Przełącznik formularzy */}
            <Tabs
              value={activeTab}
              onChange={handleTabChange}
              centered
              textColor="primary"
              indicatorColor="secondary"
              sx={{ mb: 3 }}
            >
              <Tab label="Logowanie" />
              <Tab label="Rejestracja" />
            </Tabs>

            <AnimatePresence exitBeforeEnter>
              {activeTab === 0 ? (
                <motion.div
                  key="login"
                  variants={formVariants}
                  initial="initial"
                  animate="animate"
                  exit="exit"
                >
                  <Box
                    component="form"
                    onSubmit={handleLoginSubmit}
                    sx={{
                      display: "flex",
                      flexDirection: "column",
                      gap: 2,
                      mt: 2,
                    }}
                  >
                    <TextField
                      label="Nazwa użytkownika"
                      variant="outlined"
                      fullWidth
                      required
                      color="primary"
                      value={loginData.username}
                      onChange={(e) =>
                        setLoginData({ ...loginData, username: e.target.value })
                      }
                      error={Boolean(errors.username)}
                      helperText={errors.username}
                    />
                    <TextField
                      label="Hasło"
                      variant="outlined"
                      fullWidth
                      required
                      type="password"
                      color="primary"
                      value={loginData.password}
                      onChange={(e) =>
                        setLoginData({ ...loginData, password: e.target.value })
                      }
                      error={Boolean(errors.password)}
                      helperText={errors.password}
                    />
                    <Button
                      type="submit"
                      variant="contained"
                      color="primary"
                      size="large"
                      sx={{ mt: 2 }}
                      component={motion.button}
                      whileHover={{ scale: 1.05 }}
                      whileTap={{ scale: 0.95 }}
                    >
                      Zaloguj
                    </Button>
                  </Box>
                </motion.div>
              ) : (
                <motion.div
                  key="register"
                  variants={formVariants}
                  initial="initial"
                  animate="animate"
                  exit="exit"
                >
                  <Box
                    component="form"
                    onSubmit={handleRegisterSubmit}
                    sx={{
                      display: "flex",
                      flexDirection: "column",
                      gap: 2,
                      mt: 2,
                    }}
                  >
                    <TextField
                      label="Nazwa użytkownika"
                      variant="outlined"
                      fullWidth
                      required
                      color="primary"
                      value={registerData.username}
                      onChange={(e) =>
                        setRegisterData({
                          ...registerData,
                          username: e.target.value,
                        })
                      }
                      error={Boolean(errors.username)}
                      helperText={errors.username}
                    />
                    <TextField
                      label="Adres e-mail"
                      variant="outlined"
                      fullWidth
                      required
                      color="primary"
                      value={registerData.email}
                      onChange={(e) =>
                        setRegisterData({
                          ...registerData,
                          email: e.target.value,
                        })
                      }
                      error={Boolean(errors.email)}
                      helperText={errors.email}
                    />
                    <TextField
                      label="Hasło"
                      variant="outlined"
                      fullWidth
                      required
                      type="password"
                      color="primary"
                      value={registerData.password}
                      onChange={(e) =>
                        setRegisterData({
                          ...registerData,
                          password: e.target.value,
                        })
                      }
                      error={Boolean(errors.password)}
                      helperText={errors.password}
                    />
                    <TextField
                      label="Powtórz hasło"
                      variant="outlined"
                      fullWidth
                      required
                      type="password"
                      color="primary"
                      value={registerData.confirmPassword}
                      onChange={(e) =>
                        setRegisterData({
                          ...registerData,
                          confirmPassword: e.target.value,
                        })
                      }
                      error={Boolean(errors.confirmPassword)}
                      helperText={errors.confirmPassword}
                    />
                    <Button
                      type="submit"
                      variant="contained"
                      color="secondary"
                      size="large"
                      sx={{ mt: 2 }}
                      component={motion.button}
                      whileHover={{ scale: 1.05 }}
                      whileTap={{ scale: 0.95 }}
                    >
                      Zarejestruj się
                    </Button>
                  </Box>
                </motion.div>
              )}
            </AnimatePresence>
          </Paper>
        </Grow>
      </Container>
    </ThemeProvider>
  );
}

export default Login;
