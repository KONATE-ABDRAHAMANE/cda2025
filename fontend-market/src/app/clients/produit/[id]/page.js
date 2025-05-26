import { useEffect, useState } from "react";

export default function ProduitsPage() {
  const [produits, setProduits] = useState([]);
  const [selectedProduit, setSelectedProduit] = useState(null);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [loading, setLoading] = useState(false);

  // Récupérer les produits
  useEffect(() => {
    fetch("http://localhost:8000/api/produits")
      .then((res) => res.json())
      .then((data) => {
        setProduits(data);
      })
      .catch(console.error);
  }, []);

  // Ajouter au panier (exemple simple)
  const ajouterAuPanier = async (produit) => {
    setLoading(true);
    try {
      // Exemple d'appel API POST vers le backend pour ajouter au panier
      await fetch("http://localhost:8000/api/panier/ajout", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ produitId: produit.id, quantite: 1 }),
      });
      alert(`Produit "${produit.nomProduit}" ajouté au panier !`);
    } catch (e) {
      alert("Erreur lors de l'ajout au panier");
    }
    setLoading(false);
  };

  return (
    <main className="min-h-screen bg-gray-50">
      <h1 className="text-4xl font-extrabold text-center my-8 text-indigo-700">
        Nos Produits
      </h1>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-10 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        {produits.map((produit) => {
          const onPromo =
            produit.prixReduction && produit.prixReduction < produit.prix;
          return (
            <div
              key={produit.id}
              className="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer hover:shadow-xl transition-shadow"
              onClick={() => {
                setSelectedProduit(produit);
                setSelectedImageIndex(0);
              }}
            >
              <div className="relative w-full h-48 overflow-hidden">
                {produit.images.length > 0 ? (
                  <img
                    src={produit.images[0]}
                    alt={produit.nomProduit}
                    className="object-cover w-full h-full transition-transform hover:scale-105"
                  />
                ) : (
                  <div className="flex items-center justify-center h-full bg-gray-200 text-gray-500">
                    Pas d’image
                  </div>
                )}
              </div>
              <div className="p-4">
                <h2 className="text-lg font-semibold text-gray-900 truncate">
                  {produit.nomProduit}
                </h2>
                <div className="mt-2 flex items-center space-x-2">
                  {onPromo ? (
                    <>
                      <span className="text-sm line-through text-gray-500">
                        {produit.prix.toFixed(2)} €
                      </span>
                      <span className="text-indigo-600 font-bold text-lg">
                        {produit.prixReduction.toFixed(2)} €
                      </span>
                    </>
                  ) : (
                    <span className="text-gray-900 font-bold text-lg">
                      {produit.prix.toFixed(2)} €
                    </span>
                  )}
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Modal produit sélectionné */}
      {selectedProduit && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center p-4 z-50">
          <div className="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-auto relative">
            <button
              onClick={() => setSelectedProduit(null)}
              className="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-2xl font-bold"
              aria-label="Fermer"
            >
              &times;
            </button>

            <div className="flex flex-col md:flex-row">
              {/* Carousel images */}
              <div className="md:w-1/2 p-4">
                <div className="overflow-hidden rounded-lg border border-gray-300 mb-4">
                  {selectedProduit.images.length > 0 ? (
                    <img
                      src={selectedProduit.images[selectedImageIndex]}
                      alt={`${selectedProduit.nomProduit} image ${selectedImageIndex + 1}`}
                      className="w-full h-80 object-contain bg-gray-100"
                    />
                  ) : (
                    <div className="h-80 flex items-center justify-center bg-gray-200 text-gray-500">
                      Pas d’image
                    </div>
                  )}
                </div>
                {/* Miniatures */}
                <div className="flex space-x-2 overflow-x-auto">
                  {selectedProduit.images.map((img, i) => (
                    <button
                      key={i}
                      onClick={() => setSelectedImageIndex(i)}
                      className={`flex-shrink-0 w-16 h-16 border rounded-md overflow-hidden ${
                        i === selectedImageIndex
                          ? "border-indigo-600"
                          : "border-gray-300"
                      }`}
                      aria-label={`Voir image ${i + 1}`}
                    >
                      <img
                        src={img}
                        alt={`Miniature ${i + 1}`}
                        className="w-full h-full object-cover"
                      />
                    </button>
                  ))}
                </div>
              </div>

              {/* Détails produit */}
              <div className="md:w-1/2 p-6 flex flex-col justify-between">
                <div>
                  <h2 className="text-2xl font-bold text-indigo-700 mb-2">
                    {selectedProduit.nomProduit}
                  </h2>
                  <p className="text-gray-700 mb-4">
                    {selectedProduit.description || "Pas de description"}
                  </p>

                  <div className="text-lg font-semibold mb-6 flex items-center space-x-4">
                    {selectedProduit.prixReduction &&
                    selectedProduit.prixReduction < selectedProduit.prix ? (
                      <>
                        <span className="line-through text-gray-500 text-xl">
                          {selectedProduit.prix.toFixed(2)} €
                        </span>
                        <span className="text-indigo-600 text-3xl font-extrabold">
                          {selectedProduit.prixReduction.toFixed(2)} €
                        </span>
                      </>
                    ) : (
                      <span className="text-gray-900 text-3xl font-extrabold">
                        {selectedProduit.prix.toFixed(2)} €
                      </span>
                    )}
                  </div>
                </div>

                <button
                  onClick={() => ajouterAuPanier(selectedProduit)}
                  disabled={loading}
                  className="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold transition-colors disabled:opacity-50"
                >
                  {loading ? "Ajout en cours..." : "Ajouter au panier"}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </main>
  );
}
