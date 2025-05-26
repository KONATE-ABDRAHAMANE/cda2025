import SelectionProduit from "./components/SelectionProduit";
import ImageGlissante from "./components/ImageGlissante";

export default function Home() {
  return (
    <div>
      <ImageGlissante />
      <section className="px-6 py-10">
        <h2 className="text-2xl font-bold text-yellow-700 mb-6">Produits phares</h2>
        <SelectionProduit endpoint="http://localhost:8000/api/produits" limit={8} />
      </section>
    </div>
  );
}

