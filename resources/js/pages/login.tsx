import React, { useState } from 'react';
import { TextField, Button, Typography, Box, Paper } from '@mui/material';
import { Link, router } from '@inertiajs/react';
import { instance } from '@/api/api';
import { isValidEmail, isValidPhone } from "./regex";
const Login = () => {
  const [emailOrPhone, setEmailOrPhone] = useState('');
  const [password, setPassword] = useState('');
  const [errors, setErrors] = useState<any>({});
  const [loading, setLoading] = useState(false);

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});
    setLoading(true);
  
    if (!emailOrPhone || !password) {
      setErrors({ general: 'All fields are required' });
      setLoading(false);
      return;
    }
  
    try {
      const payload: Record<string, string> = { password };

      if (isValidEmail(emailOrPhone)) {
        payload.email = emailOrPhone;
      } else if (isValidPhone(emailOrPhone)) {
        payload.phone_number = emailOrPhone;
      } else {
        setErrors({ login: "Incorrect email or phone number" });
        setLoading(false);
        return;
      }

      const response = await instance.post("/login", payload);
      const token = response.data.token;

      localStorage.setItem('authToken', token);
  
      router.visit('/dashboard');
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        setErrors({ general: error.response?.data?.message || 'Login failed' });
      }
    } finally {
      setLoading(false);
    }
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
          boxShadow: 'none !important',
          border: '1px solid #333',
          margin: '0px',
        }}
      >
        <form onSubmit={handleLogin}>
          <Typography variant="h6" mb={3} color="#fff">
            Login
          </Typography>

          <TextField
            fullWidth
            label="Email or Phone"
            variant="filled"
            value={emailOrPhone}
            onChange={(e) => setEmailOrPhone(e.target.value)}
            sx={{
              marginBottom: 2,
              backgroundColor: '#3a3a3a',
              borderRadius: '8px',
              input: { color: '#fff' },
              label: { color: '#bbb' },
            }}
            error={!!errors.email}
            helperText={errors.email}
          />

          <TextField
            fullWidth
            type="password"
            label="Password"
            variant="filled"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            sx={{
              marginBottom: 3,
              backgroundColor: '#3a3a3a',
              borderRadius: '8px',
              input: { color: '#fff' },
              label: { color: '#bbb' },
            }}
            error={!!errors.password}
            helperText={errors.password}
          />

          {errors.general && (
            <Typography color="error" variant="body2" mb={2}>
              {errors.general}
            </Typography>
          )}

          <Button
            type="submit"
            variant="contained"
            fullWidth
            disabled={loading}
            sx={{
              backgroundColor: '#00c8ff',
              fontWeight: 'bold',
              '&:hover': {
                backgroundColor: '#00b2e3',
              },
            }}
          >
            {loading ? 'Signing in...' : 'Sign In'}
          </Button>
        </form>

        <Typography mt={2} color="#aaa" textAlign="center">
          Don't have an account?{' '}
          <Link
            href="/register"
            style={{
              color: '#00c8ff',
              textDecoration: 'none',
              fontWeight: 'bold',
            }}
          >
            Register
          </Link>
        </Typography>
      </Paper>
    </Box>
  );
};

export default Login;
