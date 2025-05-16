/* Exo1
"use client";
import React, { useState } from 'react';

function AddTodoForm({ add }) {
  const [text, setText] = useState("");

  const handleClick = () => {
    if (text.trim() === "") return; // Ne rien faire si champ vide
    add(text); // Appelle la fonction "add" fournie par le parent
    setText(""); // Vide le champ après ajout
  };

  return (
    <div className='bg-gray-300'>
      <input
        type="text"
        value={text}
        onChange={(e) => setText(e.target.value)}
        className="border px-2 py-1"
      />
      <button
        type="button"
        onClick={handleClick}
        className="ml-2 bg-blue-500 text-white px-3 py-1 rounded"
      >
        Ajouter
      </button>
    </div>
  );
}

export default AddTodoForm; */

"use client";
import React, { useState } from "react";

function AddTodoForm({ addTodo }) {
  const [text, setText] = useState("");

  const handleClick = () => {
    if (text.trim() === "") return;
    addTodo(text);
    setText(""); // Réinitialiser le champ
  };

  return (
    <div className="mb-4">
      <input
  type="text"
  value={text}
  onChange={(e) => setText(e.target.value)}
  placeholder="Ajouter une tâche..."
  className="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400"
/>

<button
  onClick={handleClick}
  className="mt-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg transition"
>
  ➕ Ajouter
</button>

    </div>
  );
}

export default AddTodoForm;

