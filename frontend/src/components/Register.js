// src/components/Register.js
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../api';

const Register = () => {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [error, setError] = useState('');
    const navigate = useNavigate();


  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    if (password !== confirmPassword) {
        setError('Passwords do not match.');
        return;
      }
    try {
        const response = await api.post('/register', {
            name,
            email,
            password,
          });

        const { token } = response.data;

      // Save the token (localStorage or state management)
      localStorage.setItem('authToken', token);

      // Redirect to the dashboard after successful registration
      navigate('/');
    } catch (error) {
      setError('Failed to register. Please try again.');
    }
  };

  return (
    <div className="container">
      <div className="form-container">
      <h2>Register</h2>
      {error && <p className="error">{error}</p>}
      <form onSubmit={handleSubmit}>
        <input
            type="text"
            placeholder="Name"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
          />
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <input
            type="password"
            placeholder="Confirm Password"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            required
          />
            <button type="submit">Register</button>
          <a href="/login">Already have an account? Login here</a>          
      </form>
    </div>
    </div>
  );
};

export default Register;
