"use client";
import { useEffect, useState } from "react";
import axios from "axios";
import ListeProduits from "./ListeProduits";

export default function SelectionProduit({ endpoint, limit }) {
  const [produits, setProduits] = useState([]);

  useEffect(() => {
    axios.get(endpoint)
      .then(res => {
        const data = limit ? res.data.slice(0, limit) : res.data;
        setProduits(data);
      })
      .catch(error => console.error("Erreur API:", error));
  }, [endpoint, limit]);

  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
      {produits.map(prod => (
        <ListeProduits key={prod.id} produit={prod} />
      ))}
    </div>
  );
}


