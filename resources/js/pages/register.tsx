import React from 'react';
import { TextField, Button, Typography, Box, Paper } from '@mui/material';
import { useForm, Link } from '@inertiajs/react';

const Register = () => {
  const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    form.post('/register', {
      onError: (e) => console.log('błąd', e),
    });
  };

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
        <form onSubmit={handleSubmit}>
          <Typography variant="h6" mb={2} color="#fff">
            Create Account
          </Typography>

          <TextField
            fullWidth
            label="Email"
            variant="filled"
            value={form.data.email}
            onChange={(e) => form.setData('email', e.target.value)}
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
            value={form.data.password}
            onChange={(e) => form.setData('password', e.target.value)}
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
            value={form.data.password_confirmation}
            onChange={(e) => form.setData('password_confirmation', e.target.value)}
            sx={{
              mb: 3,
              backgroundColor: '#3a3a3a',
              input: { color: '#fff' },
              label: { color: '#bbb' },
            }}
          />

          <Button type="submit" variant="contained" fullWidth sx={{ backgroundColor: '#00c8ff', color: '#000', fontWeight: 'bold' }}>
            Register
          </Button>

          <Typography mt={2} color="#aaa" textAlign="center">
            Already have an account?{' '}
            <Link href="/login" style={{ color: '#00c8ff', textDecoration: 'none', fontWeight: 'bold' }}>
              Sign in
            </Link>
          </Typography>
        </form>
      </Paper>
    </Box>
  );
};

export default Register;
