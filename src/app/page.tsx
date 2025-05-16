/* TP1
"use client";
import "./globals.css"
import TodoList from "./componnents/TodoList"
import AddTodoForm from "./componnents/AddTodoForm"
import { useState } from "react";
export default function Home() {
  const [todos, setTodos] = useState([]);

  const add = (newTodo) => {
   setTodos([...todos, newTodo]); // ✅ Ajoute correctement à la liste
  };
  return (
    <div className="grid grid-rows items-center justify-items-center p-2 pb-5 gap-5 sm:p-8 font-[family-name:var(--font-geist-sans)]">
      <AddTodoForm add={add} />
      <div className="p-2 bg-amber-300 items">
        <TodoList table={todos}/>
      </div>
    </div>
  );
} */

"use client";
import { useState } from "react";
import AddTodoForm from "./componnents/AddTodoForm";
import TodoList from "./componnents/TodoList";

export default function App() {
  const [todos, setTodos] = useState([
    { id: 1, title: "Apprendre React", done: false, date: new Date() },
    { id: 2, title: "Faire les courses", done: true, date: new Date() },
  ]);

  const addTodo = (title) => {
    const newTodo = {
      id: Date.now(),
      title,
      done: false,
      date: new Date(),
    };
    setTodos((prev) =>
      [...prev, newTodo].sort((a, b) => b.date - a.date)
    );
  };

  const toggleDone = (id) => {
    setTodos((prev) =>
      prev.map((todo) =>
        todo.id === id ? { ...todo, done: !todo.done } : todo
      )
    );
  };

  const deleteTodo = (id) => {
    setTodos((prev) => prev.filter((todo) => todo.id !== id));
  };

  const editTodo = (id, newTitle) => {
    setTodos((prev) =>
      [...prev.map((todo) =>
        todo.id === id ? { ...todo, title: newTitle, date: new Date() } : todo
      )].sort((a, b) => b.date - a.date)
    );
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-100 via-purple-100 to-pink-100 flex items-center justify-center p-4">
  <div className="bg-white shadow-xl rounded-2xl p-8 max-w-xl w-full">
    <h1 className="text-3xl font-bold text-center text-gray-800 mb-6">📝 Ma To-Do List</h1>
    <AddTodoForm addTodo={addTodo} />
    <TodoList
      todos={todos}
      onToggle={toggleDone}
      onDelete={deleteTodo}
      onEdit={editTodo}
    />
  </div>
</div>

  );
}


