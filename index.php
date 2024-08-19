<?php
declare(strict_types=1);

require_once 'config/config.php';
require_once 'src/database/Database.php';
require_once 'src/controllers/AuthController.php';
require_once 'src/controllers/TodoController.php';
require_once 'src/utils/functions.php';

session_start();

$db = Database::getInstance()->getConnection();
$authController = new AuthController($db);
$todoController = new TodoController($db);

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($authController->login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        $success = "Logged in successfully!";
    } else {
        $error = "Invalid username or password.";
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    if ($authController->register($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        $success = "Registered successfully! Please log in.";
    } else {
        $error = "Registration failed. Username might already exist.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    $authController->logout();
    header('Location: index.php');
    exit;
}

// Handle adding a new todo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_todo'], $_SESSION['user_id'])) {
    $id = $todoController->addTodo($_SESSION['user_id'], $_POST['task'] ?? '');
    echo renderTodoItem($id, $_POST['task'], false, $id);
    exit;
}

// Handle updating todo status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_todo'], $_SESSION['user_id'])) {
    $id = intval($_POST['id'] ?? 0);
    $completed = (isset($_POST['completed']) && $_POST['completed'] === '1') ? 1 : 0;
    $result = $todoController->updateTodo($_SESSION['user_id'], $id, $completed);
    header('Content-Type: application/json');
    echo json_encode(['success' => $result !== false, 'completed' => $completed]);
    exit;
}

// Handle updating todo positions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_positions'], $_SESSION['user_id'])) {
    $positions = json_decode($_POST['positions'], true);
    $result = $todoController->updatePositions($_SESSION['user_id'], $positions);
    header('Content-Type: application/json');
    echo json_encode(['success' => $result !== false]);
    exit;
}

// Handle deleting a todo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_todo'], $_SESSION['user_id'])) {
    $id = intval($_POST['id'] ?? 0);
    $todoController->deleteTodo($_SESSION['user_id'], $id);
    exit;
}

// Render the appropriate view
if (!isset($_SESSION['user_id'])) {
    include 'src/views/layout.php';
} else {
    $todos = $todoController->getTodos($_SESSION['user_id']);
    include 'src/views/layout.php';
}