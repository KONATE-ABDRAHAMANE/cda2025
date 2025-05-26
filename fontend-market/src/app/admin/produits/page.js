// pages/admin/produits/index.js
"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";

export default function AdminProduitsPage() {
  const [produits, setProduits] = useState([]);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    fetch("http://localhost:8000/api/produits")
      .then((res) => res.json())
      .then((data) => setProduits(data))
      .catch(console.error)
      .finally(() => setLoading(false));
  }, []);

  const supprimerProduit = async (id) => {
    if (!confirm("Supprimer ce produit ?")) return;
    await fetch(`http://localhost:8000/api/produits/produit/${id}`, {
      method: "DELETE",
    });
    setProduits((prev) => prev.filter((p) => p.id !== id));
  };

  return (
    <div className="min-h-screen bg-yellow-50 p-6">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-3xl font-bold text-yellow-400">Gestion des Produits</h1>
        <Link
          href="/admin/produits/ajouter"
          className="bg-green-600 text-white px-4 py-2 rounded-lg shadow hover:bg-red-600 transition-colors"
        >
          Ajouter un produit
        </Link>
      </div>

      {loading ? (
        <p className="text-gray-600">Chargement...</p>
      ) : (
        <div className="grid gap-8 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          {produits.map((produit) => (
            <div
              key={produit.id}
              id={produit.id}
              className="bg-white rounded-xl shadow-md hover:shadow-xl transition cursor-pointer overflow-hidden border border-yellow-100"
              onClick={() => router.push(`/admin/produits/${produit.id}`)}
            >
              <div className="h-48 bg-gray-100 flex justify-center items-center overflow-hidden">
                {produit.images && produit.images.length > 0 ? (
                  <img
                    src={produit.images[0]}
                    alt={produit.nomProduit}
                    className="object-cover w-full h-full transition-transform duration-300 hover:scale-105"
                  />
                ) : (
                  <div className="text-gray-400">Pas d'image</div>
                )}
              </div>
              <div className="p-4">
                <h2 className="text-xl font-bold text-yellow-400 mb-1 truncate">
                  {produit.nomProduit}
                </h2>
                <div className="text-sm text-gray-600 mb-2 truncate">
                  Stock : {produit.stock}
                </div>
                <div className="text-md font-semibold">
                  {produit.prixReduction && produit.prixReduction < produit.prix ? (
                    <>
                      <span className="line-through text-red-600 mr-2">
                        {parseFloat(produit.prix).toFixed(2)} €
                      </span>
                      <span className="text-green-600">
                        {parseFloat(produit.prixReduction).toFixed(2)} €
                      </span>
                    </>
                  ) : (
                    <span className="text-gray-800">
                      {parseFloat(produit.prix).toFixed(2)} €
                    </span>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
