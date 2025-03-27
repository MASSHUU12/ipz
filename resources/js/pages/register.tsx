import React from 'react';
import {
  TextField,
  Button,
  Typography,
  Box,
  Paper
} from '@mui/material';
import { Link } from '@inertiajs/react';

const Register = () => {
  return (
    <Box
      sx={{
        minHeight: '100vh',
        backgroundColor: '#1e1e1e',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        margin: 0,
        padding: 0,
      }}
    >
      <Paper
        elevation={0}
        sx={{
          padding: 4,
          width: 350,
          backgroundColor: '#2e2e2e',
          borderRadius: '10px',
          boxShadow: 'none',
        }}
      >
        <Typography variant="h6" mb={2} color="#fff">
          Create Account
        </Typography>

        <TextField
          fullWidth
          label="Name"
          variant="filled"
          sx={{
            mb: 2,
            backgroundColor: '#3a3a3a',
            input: { color: '#fff' },
            label: { color: '#bbb' },
          }}
        />

        <TextField
          fullWidth
          label="Email or Phone"
          variant="filled"
          sx={{
            mb: 2,
            backgroundColor: '#3a3a3a',
            input: { color: '#fff' },
            label: { color: '#bbb' },
          }}
        />

        <TextField
          fullWidth
          type="password"
          label="Password"
          variant="filled"
          sx={{
            mb: 2,
            backgroundColor: '#3a3a3a',
            input: { color: '#fff' },
            label: { color: '#bbb' },
          }}
        />

        <TextField
          fullWidth
          type="password"
          label="Confirm Password"
          variant="filled"
          sx={{
            mb: 3,
            backgroundColor: '#3a3a3a',
            input: { color: '#fff' },
            label: { color: '#bbb' },
          }}
        />

        <Button
          variant="contained"
          fullWidth
          sx={{ backgroundColor: '#00c8ff', color: '#000', fontWeight: 'bold' }}
        >
          Register
        </Button>
        <Typography mt={2} color="#aaa" textAlign="center">
        Already have an account?{' '}
        <Link href="/login" style={{ color: '#00c8ff', textDecoration: 'none', fontWeight: 'bold' }}>
            Sign in
        </Link>
        </Typography>

      </Paper>
    </Box>
  );
};

export default Register;
