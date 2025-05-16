import { useState } from "react";

function TodoItem({ todo, onToggle, onDelete, onEdit }) {
  const [isEditing, setIsEditing] = useState(false);
  const [editedTitle, setEditedTitle] = useState(todo.title);

  const handleEdit = () => {
    setIsEditing(true);
  };

  const handleSave = () => {
    if (editedTitle.trim() !== "") {
      onEdit(todo.id, editedTitle);
    }
    setIsEditing(false);
  };

  const handleKeyDown = (e) => {
    if (e.key === "Enter") handleSave();
  };

  return (
    <li
  className={`flex flex-col sm:flex-row sm:items-center justify-between gap-2 border p-4 rounded-lg mb-3 shadow-sm transition ${
    todo.done ? "bg-gray-100" : "bg-white"
  }`}
>
  <div className="flex items-center gap-3">
    <input
      type="checkbox"
      checked={todo.done}
      onChange={() => onToggle(todo.id)}
      className="accent-blue-500 scale-125"
    />
    {isEditing ? (
      <input
        type="text"
        value={editedTitle}
        onChange={(e) => setEditedTitle(e.target.value)}
        onKeyDown={handleKeyDown}
        className="border rounded px-2 py-1 text-sm w-full"
      />
    ) : (
      <span
        className={`text-lg ${
          todo.done ? "line-through text-gray-500" : "text-gray-800"
        }`}
      >
        {todo.title}
      </span>
    )}
  </div>

  <div className="flex gap-2 justify-end">
    {isEditing ? (
      <button
        onClick={handleSave}
        className="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition"
      >
        ✅
      </button>
    ) : (
      <button
        onClick={handleEdit}
        className="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded transition"
      >
        ✏️
      </button>
    )}
    <button
      onClick={() => onDelete(todo.id)}
      className="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition"
    >
      🗑️
    </button>
  </div>

  <small className="text-xs text-gray-400 text-right">
    {todo.date.toLocaleString()}
  </small>
</li>

  );
}

export default TodoItem;

