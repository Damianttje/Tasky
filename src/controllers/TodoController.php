<?php
class TodoController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addTodo($user_id, $task) {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(position), 0) + 1 as new_position FROM todos WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $new_position = $result->fetchArray(SQLITE3_ASSOC)['new_position'];

        $stmt = $this->db->prepare("INSERT INTO todos (user_id, task, completed, position) VALUES (:user_id, :task, 0, :position)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':task', $task, SQLITE3_TEXT);
        $stmt->bindValue(':position', $new_position, SQLITE3_INTEGER);
        $stmt->execute();

        return $this->db->lastInsertRowID();
    }

    public function updateTodo($user_id, $id, $completed) {
        $stmt = $this->db->prepare("UPDATE todos SET completed = :completed WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':completed', $completed, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public function deleteTodo($user_id, $id) {
        $stmt = $this->db->prepare("DELETE FROM todos WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public function updatePositions($user_id, $positions) {
        $this->db->exec('BEGIN TRANSACTION');
        foreach ($positions as $id => $position) {
            $stmt = $this->db->prepare("UPDATE todos SET position = :position WHERE id = :id AND user_id = :user_id");
            $stmt->bindValue(':position', $position, SQLITE3_INTEGER);
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
            $stmt->execute();
        }
        return $this->db->exec('COMMIT');
    }

    public function getTodos($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM todos WHERE user_id = :user_id ORDER BY position ASC");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $todos = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $todos[] = $row;
        }
        return $todos;
    }
}