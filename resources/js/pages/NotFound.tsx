import React from "react";
import { Box, Typography, Button, Paper, Container } from "@mui/material";
import { router } from "@inertiajs/react";

const NotFound = () => {
  return (
    <Box
      sx={{
        minHeight: "100vh",
        backgroundColor: "#1e1e1e",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        p: 4,
      }}
    >
      <Container maxWidth="md">
        <Paper
          elevation={8}
          sx={{
            backgroundColor: "#222",
            borderRadius: 4,
            p: { xs: 4, md: 6 },
            textAlign: "center",
            boxShadow: "0 0 30px rgba(0,0,0,0.5)",
          }}
        >
          <Typography
            variant="h1"
            sx={{ fontWeight: "bold", fontSize: { xs: "6rem", md: "8rem" }, mb: 2, color: "#00c8ff" }}
          >
            404
          </Typography>

          <Typography
            variant="h5"
            sx={{ color: "#fff", mb: 3, fontSize: { xs: "1.25rem", md: "1.5rem" } }}
          >
            The page has not been found
          </Typography>

          <Typography
            variant="body1"
            sx={{ color: "#bbb", mb: 4, fontSize: { xs: "1rem", md: "1.2rem" } }}
          >
            The address is incorrect or has been changed
          </Typography>

          <Button
            variant="contained"
            onClick={() => router.visit("/")}
            sx={{
              backgroundColor: "#00c8ff",
              color: "#000",
              fontWeight: "bold",
              fontSize: "1rem",
              px: 5,
              py: 1.5,
              borderRadius: 3,
              "&:hover": {
                backgroundColor: "#00b0e6",
              },
            }}
          >
            Return to the dashboard
          </Button>
        </Paper>
      </Container>
    </Box>
  );
};

export default NotFound;
