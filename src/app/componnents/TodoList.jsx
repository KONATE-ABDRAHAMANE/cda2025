/* TP1
"use client";
import React from 'react'

function TodoList({table}) {
  return (
    <div className='grid ml-2.5 mr-2.5'>
        {table.map((item)=>
            <div className='m-2'><a>{item}</a></div>
        )}
    </div>
  )
}

export default TodoList */

import TodoItem from "./TodoItem";

function TodoList({ todos, onToggle, onDelete, onEdit }) {
  return (
    <ul className="space-y-2">
      {todos.map((todo) => (
        <TodoItem
          key={todo.id}
          todo={todo}
          onToggle={onToggle}
          onDelete={onDelete}
          onEdit={onEdit}
        />
      ))}
    </ul>
  );
}

export default TodoList;
