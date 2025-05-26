"use client";

import { useState, useEffect } from "react";
import { useSearchParams } from "next/navigation";
import axios from "axios";

export default function ResetConfirm() {
  const searchParams = useSearchParams();
  const token = searchParams.get("token");

  const [password, setPassword] = useState("");
  const [done, setDone] = useState(false);
  const [error, setError] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    try {
      await axios.post(`http://192.168.182.1:8000/api/reset-password/${token}`, { password });
      setDone(true);
    } catch (err) {
      setError("Token invalide ou expiré.");
    }
  }

  if (!token) return <p>Token manquant.</p>;

  return (
    <div style={{ maxWidth: "400px", margin: "auto", padding: "2rem" }}>
      <h2>Nouveau mot de passe</h2>
      {done ? (
        <p>Mot de passe modifié. <a href="/pages/login">Se connecter</a></p>
      ) : (
        <form onSubmit={handleSubmit}>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="Nouveau mot de passe"
            required
            style={{ width: "100%", padding: "0.5rem", marginBottom: "1rem" }}
          />
          {error && <p style={{ color: "red" }}>{error}</p>}
          <button type="submit" style={{ background: "#2ecc71", padding: "0.75rem", border: "none", width: "100%" }}>
            Changer le mot de passe
          </button>
        </form>
      )}
    </div>
  );
}
