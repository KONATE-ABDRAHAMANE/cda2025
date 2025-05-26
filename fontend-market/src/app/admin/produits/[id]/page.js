// pages/admin/produits/[id].js
"use client";

import { useEffect, useState } from "react";
import { useRouter, useParams } from "next/navigation";

export default function ProduitDetailPage() {
  const { id } = useParams();
  const router = useRouter();
  const [produit, setProduit] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`http://localhost:8000/api/produits/${id}`)
      .then((res) => res.json())
      .then(setProduit)
      .catch(console.error)
      .finally(() => setLoading(false));
  }, [id]);

  const supprimerProduit = async () => {
    if (!confirm("Supprimer ce produit ?")) return;
    await fetch(`http://localhost:8000/api/produits/${id}`, {
      method: "DELETE",
    });
    router.push("/admin/produits");
  };

  if (loading) return <p>Chargement...</p>;
  if (!produit) return <p>Produit introuvable.</p>;

  return (
    <div className="min-h-screen bg-gray-100 p-6">
      <div className="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
        <h1 className="text-3xl font-bold text-yellow-600 mb-4">
          {produit.nomProduit}
        </h1>

        <div className="grid md:grid-cols-2 gap-6">
          <div>
            {produit.images && produit.images.length > 0 ? (
              <img
                src={produit.images[0]}
                alt={produit.nomProduit}
                className="w-full h-64 object-contain bg-gray-100 rounded"
              />
            ) : (
              <div className="h-64 bg-gray-200 flex items-center justify-center text-gray-500">
                Pas d’image
              </div>
            )}
          </div>

          <div>
            <p className="text-gray-700 mb-2">{produit.description}</p>
            <p className="mb-2">Stock : {produit.stock}</p>
            <p className="text-lg font-bold">
              {produit.prixReduction && produit.prixReduction < produit.prix ? (
                <>
                  <span className="line-through text-red-500 mr-2">
                    {parseFloat(produit.prix).toFixed(2)} €
                  </span>
                  <span className="text-green-600">
                    {parseFloat(produit.prixReduction).toFixed(2)} €
                  </span>
                </>
              ) : (
                <span>{parseFloat(produit.prix).toFixed(2)} €</span>
              )}
            </p>
          </div>
        </div>

        <div className="mt-6 flex gap-4">
          <button
            onClick={() => router.push(`/admin/produits/modifier/${id}`)}
            className="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded"
          >
            Modifier
          </button>
          <button
            onClick={supprimerProduit}
            className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  );
}
