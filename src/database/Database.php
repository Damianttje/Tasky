<?php
class Database {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = new SQLite3(DB_NAME);
        $this->createTables();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function createTables() {
        $this->db->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT)');
        $this->db->exec('CREATE TABLE IF NOT EXISTS todos (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, task TEXT, completed INTEGER DEFAULT 0, position INTEGER)');
    }

    public function getConnection() {
        return $this->db;
    }
}