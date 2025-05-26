"use client";
import { useState } from 'react';
import { useRouter } from 'next/navigation';
import axios from 'axios';
import { jwtDecode } from 'jwt-decode';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const handleLogin = async (e) => {
    e.preventDefault();
    try {
      const res = await axios.post('http://127.0.0.1:8000/api/login', { email, password });
      const token = res.data.token;
      localStorage.setItem('token', token);

      const decoded = jwtDecode(token);
      const roles = decoded.roles;

      if (roles.includes('ROLE_ADMIN')) router.push('/dashboard/admin');
      else if (roles.includes('ROLE_LIVREUR')) router.push('/dashboard/livreur');
      else router.push('/dashboard/client');
    } catch (err) {
      setError('Identifiants incorrects');
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
      <form onSubmit={handleLogin} className="bg-white p-10 rounded-2xl shadow-xl space-y-4 w-96">
        <h2 className="text-2xl font-bold text-center">Connexion</h2>
        {error && <p className="text-red-500 text-center">{error}</p>}
        <input
          type="email"
          placeholder="Email"
          className="w-full p-3 border border-gray-300 rounded-xl"
          value={email}
          onChange={e => setEmail(e.target.value)}
        />
        <input
          type="password"
          placeholder="Mot de passe"
          className="w-full p-3 border border-gray-300 rounded-xl"
          value={password}
          onChange={e => setPassword(e.target.value)}
        />
        <button type="submit" className="w-full bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-xl font-semibold">
          Se connecter
        </button>
      </form>
    </div>
  );
}
