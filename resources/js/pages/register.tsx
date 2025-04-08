import React, { useState } from 'react';
import {
  TextField,
  Button,
  Typography,
  Box,
  Paper
} from '@mui/material';
import { Link, router } from '@inertiajs/react';

const Register = () => {
  const [name, setName] = useState('');
  const [emailOrPhone, setEmailOrPhone] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [errors, setErrors] = useState<any>({});
  const [loading, setLoading] = useState(false);
  
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
  
    if (password !== confirmPassword) {
      setErrors({ confirmPassword: "Passwords don't match" });
      setLoading(false);
      return;
    }
  
    try {
      await fetch('http://localhost:8000/sanctum/csrf-cookie', {
        credentials: 'include',
      });
  
      const response = await fetch('http://localhost:8000/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({
          name,
          email: emailOrPhone,
          password,
          password_confirmation: confirmPassword,
        }),
      });
  
      const data = await response.json();
  
      if (!response.ok) {
        setErrors(data.errors || { general: data.message || 'Something went wrong' });
      } else {
        router.visit('/login');
      }
    } catch (error) {
      console.error(error);
      setErrors({ general: 'Network error' });
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
      }}
    >
      <Paper
        elevation={0}
        sx={{
          padding: 4,
          width: 350,
          backgroundColor: '#2e2e2e',
          borderRadius: '10px',
        }}
        component="form"
        onSubmit={handleSubmit}
      >
        <Typography variant="h6" mb={2} color="#fff">
          Create Account
        </Typography>

        <TextField
          fullWidth
          label="Name"
          variant="filled"
          value={name}
          onChange={(e) => setName(e.target.value)}
          error={!!errors.name}
          helperText={errors.name}
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
          value={emailOrPhone}
          onChange={(e) => setEmailOrPhone(e.target.value)}
          error={!!errors.email}
          helperText={errors.email}
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
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          error={!!errors.password}
          helperText={errors.password}
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
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
          error={!!errors.confirmPassword}
          helperText={errors.confirmPassword}
          sx={{
            mb: 3,
            backgroundColor: '#3a3a3a',
            input: { color: '#fff' },
            label: { color: '#bbb' },
          }}
        />

        {errors.general && (
          <Typography color="error" mb={2}>{errors.general}</Typography>
        )}

        <Button
          type="submit"
          variant="contained"
          fullWidth
          disabled={loading}
          sx={{ backgroundColor: '#00c8ff', color: '#000', fontWeight: 'bold' }}
        >
          {loading ? 'Registering...' : 'Register'}
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
