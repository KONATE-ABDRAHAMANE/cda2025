"use client";
import { useState } from "react";
import axios from "axios";

export default function ConfirmerReinitPage() {
  const [form, setForm] = useState({ email: "", code: "", password: "" });
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState("");

  async function handleSubmit(e) {
    e.preventDefault();
    try {
      await axios.post("http://192.168.182.1:8000/api/utilisateur/confirmer-reinitialisation", form);
      setSuccess(true);
      setError("");
    } catch (err) {
      setError(err.response?.data?.error || "Erreur");
    }
  }

  return (
    <div className="max-w-md mx-auto p-6 text-center">
      <h2 className="text-2xl font-bold mb-4 text-green-600">Confirmation de réinitialisation</h2>
      {success ? (
        <p className="text-green-700">
          Mot de passe modifié ! <a href="/pages/login" className="underline text-yellow-600">Connexion</a>
        </p>
      ) : (
        <form onSubmit={handleSubmit} className="space-y-4">
          <input
            type="email"
            placeholder="Email"
            className="w-full border p-2 rounded"
            value={form.email}
            onChange={(e) => setForm({ ...form, email: e.target.value })}
            required
          />
          <input
            type="text"
            placeholder="Code reçu"
            className="w-full border p-2 rounded"
            value={form.code}
            onChange={(e) => setForm({ ...form, code: e.target.value })}
            required
          />
          <input
            type="password"
            placeholder="Nouveau mot de passe"
            className="w-full border p-2 rounded"
            value={form.password}
            onChange={(e) => setForm({ ...form, password: e.target.value })}
            required
          />
          {error && <p className="text-red-500">{error}</p>}
          <button type="submit" className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Confirmer
          </button>
        </form>
      )}
    </div>
  );
}
