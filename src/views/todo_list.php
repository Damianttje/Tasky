<div class="card bg-base-200 shadow-xl">
    <div class="card-body">
        <h2 class="card-title">Add New Task</h2>
        <form id="add-todo-form" hx-post="index.php" hx-target="#todo-list" hx-swap="beforeend" class="form-control">
            <div class="flex gap-2">
                <input type="text" name="task" placeholder="Enter a task" required class="input input-bordered flex-grow">
                <button type="submit" name="add_todo" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

<div class="card bg-base-200 shadow-xl mt-4">
    <div class="card-body">
        <h2 class="card-title">Your Tasks</h2>
        <ul id="todo-list" class="mt-2">
            <?php foreach ($todos as $todo): ?>
                <?= renderTodoItem($todo['id'], $todo['task'], (bool)$todo['completed'], $todo['position']) ?>
            <?php endforeach; ?>
        </ul>
    </div>
</div>