"use client";
import { Button } from "@/components/ui/button";

export default function FilterTodo({
  filter,
  setFilter,
}: {
  filter: "all" | "done" | "todo";
  setFilter: (f: "all" | "done" | "todo") => void;
}) {
  return (
    <div className="flex gap-4 justify-center">
      <Button variant={filter === "all" ? "default" : "outline"} onClick={() => setFilter("all")}>
        Toutes
      </Button>
      <Button variant={filter === "todo" ? "default" : "outline"} onClick={() => setFilter("todo")}>
        À faire
      </Button>
      <Button variant={filter === "done" ? "default" : "outline"} onClick={() => setFilter("done")}>
        Faites
      </Button>
    </div>
  );
}
