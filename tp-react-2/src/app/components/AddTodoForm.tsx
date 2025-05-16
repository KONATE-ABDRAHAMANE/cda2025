"use client";

import { useState } from "react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Todo } from "@/lib/utils";

export default function AddTodoForm({
  addTodo,
}: {
  addTodo: (t: Todo) => void;
}) {
  const [title, setTitle] = useState("");
  const [priority, setPriority] = useState<Todo["priority"]>("Moyenne");
  const [dueDate, setDueDate] = useState("");
  const [error, setError] = useState("");

  const handleAdd = () => {
    if (!title.trim()) {
      setError("Le titre est requis.");
      return;
    }

    const newTodo: Todo = {
      id: crypto.randomUUID(),
      title: title.trim(),
      done: false,
      priority,
      dueDate,
      updatedAt: new Date().toISOString(),
    };

    addTodo(newTodo);

    // Réinitialiser les champs
    setTitle("");
    setPriority("Moyenne");
    setDueDate("");
    setError("");
  };

  return (
    <div className="flex flex-col sm:flex-row items-center gap-4 transition-all">
      <Input
        value={title}
        onChange={(e) => {
          setTitle(e.target.value);
          setError("");
        }}
        placeholder="Ajouter une tâche"
        className="w-full"
      />
      <Select value={priority} onValueChange={(v) => setPriority(v as Todo["priority"])}>
        <SelectTrigger className="w-[130px]">
          <SelectValue placeholder="Priorité" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="Haute">Haute</SelectItem>
          <SelectItem value="Moyenne">Moyenne</SelectItem>
          <SelectItem value="Basse">Basse</SelectItem>
        </SelectContent>
      </Select>
      <Input
        type="date"
        value={dueDate}
        onChange={(e) => setDueDate(e.target.value)}
        className="min-w-[160px]"
      />
      <Button onClick={handleAdd}>Ajouter</Button>

      {error && <p className="text-red-500 text-sm mt-1">{error}</p>}
    </div>
  );
}

