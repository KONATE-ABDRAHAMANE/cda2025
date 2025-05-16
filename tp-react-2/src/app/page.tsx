"use client";

import { useEffect, useState } from "react";
import AddTodoForm from "./components/AddTodoForm";
import TodoList from "./components/TodoList";
import FilterTodo from "./components/FilterTodo";
import { Todo } from "@/lib/utils";

export default function Home() {
  const [todos, setTodos] = useState<Todo[]>([]);
  const [filter, setFilter] = useState<"all" | "done" | "todo">("all");

  useEffect(() => {
    const saved = localStorage.getItem("todos");
    if (saved) setTodos(JSON.parse(saved));
  }, []);

  useEffect(() => {
    localStorage.setItem("todos", JSON.stringify(todos));
  }, [todos]);

  const addTodo = (newTodo: Todo) => {
    setTodos((prev) => [...prev, newTodo]);
  };

  const updateTodo = (updated: Todo) => {
    setTodos((prev) =>
      prev.map((t) => (t.id === updated.id ? updated : t))
    );
  };

  const deleteTodo = (id: string) => {
    setTodos((prev) => prev.filter((t) => t.id !== id));
  };

  const filteredTodos = todos.filter((t) => {
    if (filter === "all") return true;
    return filter === "done" ? t.done : !t.done;
  });

  return (
    <main className="p-6 max-w-3xl mx-auto space-y-6">
      <AddTodoForm addTodo={addTodo} />
      <FilterTodo filter={filter} setFilter={setFilter} />
      <TodoList todos={filteredTodos} updateTodo={updateTodo} deleteTodo={deleteTodo} />
    </main>
  );
}
