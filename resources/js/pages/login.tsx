import React, { useState, useEffect } from 'react';
import { TextField, Button, Typography, Box, Paper } from '@mui/material';
import { Link, router } from '@inertiajs/react';
import { instance } from '@/api/api';
import { isValidEmail, isValidPhone } from "./regex";
import { Alert, AlertTitle } from '@mui/material';
import CheckCircleOutlineIcon from '@mui/icons-material/CheckCircleOutline';
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
        if (!emailOrPhone.startsWith('+')) {
          setErrors({ login: 'Phone number must start with + and contain only digits' });
          setLoading(false);
          return;
        }   
        payload.phone_number = emailOrPhone;
      } else {
        setErrors({ login: "Incorrect email or phone number" });
        setLoading(false);
        return;
      }

      const response = await instance.post("/login", payload);
      const { token, user } = response.data;
      localStorage.setItem('authToken', token);
      localStorage.setItem('user', JSON.stringify(user));
  
      router.visit('/dashboard');
    }catch (error: any) {
      const messageFromBackend = error.response?.data?.message;
      if (error.response?.status === 422 ) {
        setErrors((prev: any) => ({
          ...prev,
          ...error.response.data.errors,
          general: "Incorrect phone or email or password."
        }));
      }
       else {
        setErrors((prev: any) => ({
          ...prev,
          general:  'Login failed. Please try again.',
        }));
      }
    } finally {
      setLoading(false);
    }

     
  };
  const [showVerificationInfo, setShowVerificationInfo] = useState(false);

    useEffect(() => {
      const params = new URLSearchParams(window.location.search);
      if (params.get('verification') === '1') {
        setShowVerificationInfo(true);
      }
    }, []);

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
        {showVerificationInfo && (
          <Alert
            severity="success"
            icon={<CheckCircleOutlineIcon />}
            sx={{ mb: 3 }}
          >
            <AlertTitle>Check your mailbox</AlertTitle>
            We have sent you a verification email.
          </Alert>
        )}
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

          />
          {errors.login && (
            <Typography color="error" variant="body2" mb={2}>
              {errors.login}
            </Typography>
          )}

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
        <Button
          variant="outlined"
          fullWidth
          sx={{
            mt: 2,
            borderColor: '#00c8ff',
            color: '#00c8ff',
            fontWeight: 'bold',
            '&:hover': {
              backgroundColor: '#00c8ff22',
            },
          }}
          onClick={() => router.visit('/dashboard')}
        >
          Continue without an account
        </Button>


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
