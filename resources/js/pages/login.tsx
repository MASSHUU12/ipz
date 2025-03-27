import React from 'react';
import { TextField, Button, Typography, Box, Paper } from '@mui/material';
import { Link } from '@inertiajs/react';

const Login = () => {
  return (
    <Box
      sx={{
        minHeight: '100vh',
        backgroundColor: '#1e1e1e',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        margin:'0px'
      }}
    >
      <Paper
        elevation={0}
        sx={{
          padding: 4,
          width: 320,
          backgroundColor: '#2e2e2e',
          borderRadius: '12px',
          boxShadow: 'none !important',
          border: '1px solid #333',
          margin:'0px'
        }}
      >
        <Typography variant="h6" mb={3} color="#fff">
          Login
        </Typography>

        <TextField
          fullWidth
          label="Email or Phone"
          variant="filled"
          sx={{
            marginBottom: 2,
            backgroundColor: '#3a3a3a',
            borderRadius: '8px',
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
            marginBottom: 3,
            backgroundColor: '#3a3a3a',
            borderRadius: '8px',
            input: { color: '#fff' },
            label: { color: '#bbb' },
          }}
        />

        <Button
          variant="contained"
          fullWidth
          sx={{
            backgroundColor: '#00c8ff',
            fontWeight: 'bold',
            '&:hover': {
              backgroundColor: '#00b2e3',
            },
          }}
        >
          Sign In
        </Button>
        <Typography mt={2} color="#aaa" textAlign="center">
  Don't have an account?{' '}
  <Link href="/register" style={{ color: '#00c8ff', textDecoration: 'none', fontWeight: 'bold' }}>
    Register
  </Link>
</Typography>

      </Paper>
    </Box>
  );
};

export default Login;
