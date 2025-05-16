"use client";

import { useState } from "react";
import { Checkbox } from "@/components/ui/checkbox";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from "@/components/ui/select";
import { AlertDialog, AlertDialogContent, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger, AlertDialogAction } from "@/components/ui/alert-dialog";
import { Todo } from "@/lib/utils";

export default function TodoItem({
  todo,
  updateTodo,
  deleteTodo,
}: {
  todo: Todo;
  updateTodo: (t: Todo) => void;
  deleteTodo: (id: string) => void;
}) {
  const [open, setOpen] = useState(false);
  const [title, setTitle] = useState(todo.title);
  const [priority, setPriority] = useState<Todo["priority"]>(todo.priority);
  const [dueDate, setDueDate] = useState(todo.dueDate);

  // Vérifier si en retard
  const isLate = dueDate ? new Date(dueDate) < new Date() && !todo.done : false;

  const formatDate = (dateStr: string) => {
    const options: Intl.DateTimeFormatOptions = { day: "numeric", month: "long", year: "numeric" };
    return new Date(dateStr).toLocaleDateString("fr-FR", options);
  };

  const handleSave = () => {
    if (!title.trim()) return;
    updateTodo({
      ...todo,
      title: title.trim(),
      priority,
      dueDate,
      updatedAt: new Date().toISOString(),
    });
    setOpen(false);
  };

  return (
    <div className="flex items-center justify-between p-4 border rounded shadow-sm bg-white">
      <div className="flex items-center gap-4">
        <Checkbox
          checked={todo.done}
          onCheckedChange={() => updateTodo({ ...todo, done: !todo.done, updatedAt: new Date().toISOString() })}
        />
        <span className={`${todo.done ? "line-through text-gray-400" : "cursor-default"}`}>
          {todo.title}
        </span>
        <Badge variant="outline">{todo.priority}</Badge>
        {dueDate && (
          <span className={`text-sm flex items-center gap-1 ${isLate ? "text-red-600 font-bold" : "text-gray-600"}`}>
            {isLate && <span title="Tâche en retard">⚠️</span>} ⏰ {formatDate(dueDate)}
          </span>
        )}
      </div>

      {/* Bouton Modifier avec modale */}
      <Dialog open={open} onOpenChange={setOpen}>
        <DialogTrigger asChild>
          <Button size="sm" variant="outline">Modifier</Button>
        </DialogTrigger>
        <DialogContent className="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Modifier la tâche</DialogTitle>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <Input
              autoFocus
              placeholder="Titre"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
            />
            <Select value={priority} onValueChange={(value) => setPriority(value as Todo["priority"])}>
              <SelectTrigger>
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
            />
          </div>
          <DialogFooter className="flex justify-end gap-2">
            <Button variant="outline" onClick={() => setOpen(false)}>Annuler</Button>
            <Button onClick={handleSave}>Sauvegarder</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Suppression */}
      <AlertDialog>
        <AlertDialogTrigger asChild>
          <Button variant="destructive" size="sm">Supprimer</Button>
        </AlertDialogTrigger>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmer la suppression ?</AlertDialogTitle>
          </AlertDialogHeader>
          <AlertDialogAction onClick={() => deleteTodo(todo.id)}>Oui, supprimer</AlertDialogAction>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
