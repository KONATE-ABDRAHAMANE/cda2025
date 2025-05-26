"use client";

export default function ListeProduits({ produit }) {
  return (
    <div className="border p-4 shadow rounded">
      <h3 className="text-lg font-semibold">{produit.nomProduit}</h3>
      <p>{produit.description}</p>
      <p className="font-bold text-yellow-700">{produit.prix} €</p>
    </div>
  );
}



