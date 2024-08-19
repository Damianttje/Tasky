<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <script src="https://unpkg.com/htmx.org@2.0.2"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .dragging { opacity: 0.5; }
    </style>
</head>
<body class="bg-base-100 min-h-screen flex flex-col">
<div class="navbar bg-primary text-primary-content">
    <div class="flex-1">
        <a class="btn btn-ghost normal-case text-xl"><?= SITE_NAME ?></a>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="flex-none">
            <span class="mr-2">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <a href="?logout" class="btn btn-ghost btn-sm">Logout</a>
        </div>
    <?php endif; ?>
</div>

<div class="container mx-auto p-4 flex-grow">
    <?php
    if ($error) {
        echo "<div class='alert alert-error mb-4'><span>$error</span></div>";
    }
    if ($success) {
        echo "<div class='alert alert-success mb-4'><span>$success</span></div>";
    }

    if (!isset($_SESSION['user_id'])) {
        echo "<div class='flex flex-col md:flex-row gap-4'>";
            include 'login.php';
            include 'register.php';
        echo "</div>";
    } else {
        include 'todo_list.php';
    }
    ?>
</div>

<footer class="footer footer-center p-4 bg-base-300 text-base-content">
    <div>
        <p><?= FOOTER_TEXT ?></p>
    </div>
</footer>

<script>
    function deleteTodo(id) {
        htmx.ajax('POST', 'index.php', {target: `#todo-${id}`, swap: 'delete', values: {delete_todo: true, id: id}});
    }

    function updateTodo(id, completed) {
        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'update_todo': true,
                'id': id,
                'completed': completed ? '1' : '0'
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const taskSpan = document.querySelector(`#todo-${id} span`);
                    const checkbox = document.querySelector(`#todo-${id} input[type="checkbox"]`);
                    if (data.completed === 1) {
                        taskSpan.classList.add('line-through', 'text-gray-500');
                        checkbox.checked = true;
                    } else {
                        taskSpan.classList.remove('line-through', 'text-gray-500');
                        checkbox.checked = false;
                    }
                } else {
                    console.error('Failed to update todo');
                    document.querySelector(`#todo-${id} input[type="checkbox"]`).checked = !completed;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.querySelector(`#todo-${id} input[type="checkbox"]`).checked = !completed;
            });
    }

    const todoList = document.getElementById('todo-list');
    let draggedItem = null;

    todoList.addEventListener('dragstart', function(e) {
        draggedItem = e.target;
        setTimeout(() => e.target.classList.add('dragging'), 0);
    });

    todoList.addEventListener('dragend', function(e) {
        e.target.classList.remove('dragging');
        updatePositions();
    });

    todoList.addEventListener('dragover', function(e) {
        e.preventDefault();
        const afterElement = getDragAfterElement(todoList, e.clientY);
        const draggable = document.querySelector('.dragging');
        if (afterElement == null) {
            todoList.appendChild(draggable);
        } else {
            todoList.insertBefore(draggable, afterElement);
        }
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('li:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function updatePositions() {
        const todos = document.querySelectorAll('#todo-list li');
        const positions = {};

        todos.forEach((todo, index) => {
            const id = todo.id.split('-')[1];
            positions[id] = index + 1;
            todo.dataset.position = index + 1;
        });

        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'update_positions': true,
                'positions': JSON.stringify(positions)
            })
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to update positions');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Clear input field after adding a new todo
    document.getElementById('add-todo-form').addEventListener('htmx:afterOnLoad', function(event) {
        if (event.detail.successful) {
            this.reset();
        }
    });
</script>
</body>
</html>