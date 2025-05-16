import TodoItem from "./TodoItem";
import { Todo } from "@/lib/utils";

export default function TodoList({
  todos,
  updateTodo,
  deleteTodo,
}: {
  todos: Todo[];
  updateTodo: (t: Todo) => void;
  deleteTodo: (id: string) => void;
}) {
  return (
    <div className="space-y-4">
      {todos.map((todo) => (
        <TodoItem
          key={todo.id}
          todo={todo}
          updateTodo={updateTodo}
          deleteTodo={deleteTodo}
        />
      ))}
    </div>
  );
}


