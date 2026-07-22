<?php
$path = __DIR__ . '/../database/database.sqlite';
$pdo = new PDO('sqlite:' . $path);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("UPDATE users SET role='waiter' WHERE email='mesero@example.com';");
$pdo->exec("UPDATE users SET role='chef' WHERE email='cocina@example.com';");
echo "roles updated\n";
