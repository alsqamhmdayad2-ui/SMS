<?php
try {
  $conn = new PDO("mysql:host=127.0.0.1", "root", "");
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $conn->exec("CREATE DATABASE IF NOT EXISTS school_management_system");
  echo "Database created successfully\n";
} catch(PDOException $e) {
  echo $e->getMessage() . "\n";
}
