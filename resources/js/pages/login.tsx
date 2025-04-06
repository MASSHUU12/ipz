import React from 'react';
import { TextField, Button, Typography, Box, Paper } from '@mui/material';
import { useForm, Link } from '@inertiajs/react';

const Login = () => {
  const form = useForm({
    email: '',
    password: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    form.post('/login');
  };

  return (
    <Box
      sx={{
        minHeight: '100vh',
        backgroundColor: '#1e1e1e',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        margin: '0px',
      }}
    >
      <Paper
        elevation={0}
        sx={{
          padding: 4,
          width: 320,
          backgroundColor: '#2e2e2e',
          borderRadius: '12px',
          border: '1px solid #333',
        }}
      >
        <form key={JSON.stringify(form.errors)} onSubmit={handleSubmit}>
          <Typography variant="h6" mb={3} color="#fff">
            Login
          </Typography>

          <TextField
            fullWidth
            label="Email or Phone"
            variant="filled"
            value={form.data.email}
            onChange={(e) => form.setData('email', e.target.value)} 
            sx={{
              marginBottom: 2,
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
              marginBottom: 3,
              backgroundColor: '#3a3a3a',
              input: { color: '#fff' },
              label: { color: '#bbb' },
            }}
          />

          <Button
            type="submit"
            variant="contained"
            fullWidth
            sx={{ backgroundColor: '#00c8ff', fontWeight: 'bold' }}
          >
            Sign In
          </Button>

          {form.errors.email && (
            <Typography color="error" sx={{ mt: 1 }}>
              Incorrect login or password.
            </Typography>
          )}

          <Typography mt={2} color="#aaa" textAlign="center">
            Don't have an account?{' '}
            <Link
              href="/register"
              style={{ color: '#00c8ff', textDecoration: 'none', fontWeight: 'bold' }}
            >
              Register
            </Link>
          </Typography>
        </form>
      </Paper>
    </Box>
  );
};

export default Login;
