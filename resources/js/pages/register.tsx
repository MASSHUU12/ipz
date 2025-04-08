import React, { useState } from 'react';
import {
  TextField,
  Button,
  Typography,
  Box,
  Paper
} from '@mui/material';
import { Link, router } from '@inertiajs/react';
import { instance } from '@/api/api';

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
    
  
    if (!emailOrPhone || !password || !confirmPassword) {
      setErrors({ general: 'All fields are required' });
      setLoading(false);
      return;
    }
  
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailOrPhone)) {
      setErrors({ email: 'Incorrect email address' });
      setLoading(false);
      return;
    }
    
    if (password.length < 8) {
      setErrors({ password: 'Password must have at least 8 letters' });
      setLoading(false);
      return;
    }
    if (password.length > 255) {
      setErrors({ password: 'Password must have less than 255 letters' });
      setLoading(false);
      return;
    }
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecialChar = /[@$!%*?&]/.test(password);
    if (!hasUpperCase || !hasLowerCase || !hasNumber || !hasSpecialChar) {
      setErrors({
        password: 'Password must contain uppercase, lowercase, number and special character',
      });
      setLoading(false);
      return;
    }
  
    if (password !== confirmPassword) {
      setErrors({ confirmPassword: 'Passwords are not the same' });
      setLoading(false);
      return;
    }
   
    
    try {
      const response = await instance.post('/register', {
        name,
        email: emailOrPhone,
        password,
        password_confirmation: confirmPassword,
      });
  
      const token = response.data.token;
      localStorage.setItem('authToken', token);


      router.visit('/login');
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        setErrors({ general: 'An error occurred during registration' });
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
          label="Email"
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
