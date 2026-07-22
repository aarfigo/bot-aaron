<?php
$db = __DIR__ . '/../database/database.sqlite';
if (!file_exists($db)) {
    echo "database file not found: $db\n";
    exit(1);
}
$pdo = new PDO('sqlite:' . $db);
$stmt = $pdo->query("PRAGMA table_info('tbl_order')");
$cols = [];
foreach ($stmt as $row) {
    $cols[] = [$row['name'], $row['type']];
}
foreach ($cols as $c) {
    echo $c[0] . "\t" . $c[1] . "\n";
}
