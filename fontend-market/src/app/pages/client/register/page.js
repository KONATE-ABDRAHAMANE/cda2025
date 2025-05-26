"use client";

import { useState } from "react";
import axios from "axios";

export default function ClientRegister() {
  const [formData, setFormData] = useState({
    email: "",
    nom: "",
    prenom: "",
    telephone: "",
    password: "",
  });

  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const [loading, setLoading] = useState(false);

  const colors = {
    green: "#2ecc71",
    red: "#e74c3c",
    yellow: "#f1c40f",
    background: "#fdfdfd",
    text: "#333",
  };

  function handleChange(e) {
    setFormData((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);
    setSuccess(null);
    setLoading(true);

    try {
      const res = await axios.post("http://192.168.182.1:8000/api/client", formData, {
        headers: { "Content-Type": "application/json" },
      });
      setSuccess("Compte créé avec succès !");
      setFormData({
        email: "",
        nom: "",
        prenom: "",
        telephone: "",
        password: "",
      });
    } catch (err) {
      setError("Erreur lors de l'inscription.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <main style={{ backgroundColor: colors.background, minHeight: "100vh", padding: "1rem", display: "flex", justifyContent: "center", alignItems: "center" }}>
      <form
        onSubmit={handleSubmit}
        style={{
          width: "100%",
          maxWidth: "420px",
          padding: "2rem",
          borderRadius: "12px",
          backgroundColor: "#fff",
          boxShadow: "0 10px 25px rgba(0,0,0,0.1)",
        }}
      >
        <h2 className="text-center text-2xl mb-4">
          Créer un compte client
        </h2>

        <Input name="email" type="email" placeholder="Email" value={formData.email} onChange={handleChange} />
        <Input name="nom" placeholder="Nom" value={formData.nom} onChange={handleChange} />
        <Input name="prenom" placeholder="Prénom" value={formData.prenom} onChange={handleChange} />
        <Input name="telephone" placeholder="Téléphone" value={formData.telephone} onChange={handleChange} />
        <Input name="password" type="password" placeholder="Mot de passe" value={formData.password} onChange={handleChange} />

        {error && <p style={{ color: colors.red, marginTop: "0.5rem" }}>{error}</p>}
        {success && <p style={{ color: colors.green, marginTop: "0.5rem" }}>{success}</p>}

        <button
          type="submit"
          disabled={loading}
          style={{
            backgroundColor: colors.yellow,
            color: "#000",
            padding: "0.75rem",
            border: "none",
            borderRadius: "6px",
            fontWeight: "bold",
            width: "100%",
            marginTop: "1rem",
            cursor: loading ? "not-allowed" : "pointer",
            transition: "background-color 0.3s ease",
          }}
        >
          {loading ? "Création en cours..." : "Créer le compte"}
        </button>
      </form>
    </main>
  );
}

function Input({ name, type = "text", placeholder, value, onChange }) {
  return (
    <input
      name={name}
      type={type}
      placeholder={placeholder}
      value={value}
      onChange={onChange}
      required
      style={{
        width: "100%",
        padding: "0.75rem",
        marginBottom: "1rem",
        borderRadius: "6px",
        border: "1px solid #ddd",
        fontSize: "1rem",
        outlineColor: "#2ecc71",
      }}
    />
  );
}
