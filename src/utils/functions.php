<?php
function renderTodoItem($id, $task, $completed, $position) {
    $checkedAttr = $completed ? 'checked' : '';
    $taskClass = $completed ? 'line-through text-gray-500' : '';
    return "<li id='todo-$id' class='flex items-start justify-between p-2 bg-base-300 rounded-lg mb-2' draggable='true' data-position='$position'>
                <div class='flex items-start flex-grow mr-2'>
                    <input type='checkbox' class='checkbox mr-2 mt-1' $checkedAttr onchange='updateTodo($id, this.checked)'>
                    <span class='$taskClass break-words'>" . htmlspecialchars($task, ENT_QUOTES, 'UTF-8') . "</span>
                </div>
                <button onclick='deleteTodo($id)' class='btn btn-error btn-sm shrink-0'>Delete</button>
            </li>";
}