<?php
// Simple smoke test script: login as waiter, create order, login as chef, change status.
// Uses curl and sqlite PDO. Meant to be run from project root: php scripts/smoke_staff_flow.php

$base = 'http://127.0.0.1:8000';
$cookieDir = __DIR__ . '/tmp_cookies';
if (!is_dir($cookieDir)) mkdir($cookieDir, 0777, true);

function http_get($path, $cookieFile) {
    global $base;
    $ch = curl_init($base . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$res, $info];
}

function http_post($path, $data, $cookieFile) {
    global $base;
    $ch = curl_init($base . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$res, $info];
}

function extract_csrf($html) {
    if (preg_match('/name="_token" value="([^"]+)"/', $html, $m)) return $m[1];
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $html, $m)) return $m[1];
    return null;
}

// ensure test menu + menuitem exist in DB
function ensure_menu_item() {
    $db = __DIR__ . '/../database/database.sqlite';
    $pdo = new PDO('sqlite:'.$db);
    // ensure menus table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_menu (menuID INTEGER PRIMARY KEY, menuName TEXT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_menuitem (itemID INTEGER PRIMARY KEY, menuID INTEGER, menuItemName TEXT, price NUMERIC)");
    // insert sample if not exists
    $stmt = $pdo->prepare('SELECT menuID FROM tbl_menu WHERE menuName = :name LIMIT 1');
    $stmt->execute([':name' => 'SMOKE-MENU']);
    $mid = $stmt->fetchColumn();
    if (!$mid) {
        $pdo->exec("INSERT INTO tbl_menu (menuName) VALUES ('SMOKE-MENU')");
        $mid = $pdo->lastInsertId();
    }
    $stmt = $pdo->prepare('SELECT itemID FROM tbl_menuitem WHERE menuItemName = :name LIMIT 1');
    $stmt->execute([':name' => 'SMOKE-ITEM']);
    $iid = $stmt->fetchColumn();
    if (!$iid) {
        $stmt = $pdo->prepare('INSERT INTO tbl_menuitem (menuID, menuItemName, price) VALUES (:menuID, :name, :price)');
        $stmt->execute([':menuID' => $mid, ':name' => 'SMOKE-ITEM', ':price' => 9.50]);
        $iid = $pdo->lastInsertId();
    }
    return [$mid, $iid];
}

list($menuID, $itemID) = ensure_menu_item();
echo "Using menuID={$menuID} itemID={$itemID}\n";

// helper to get user id by email
function user_id_by_email($email) {
    $db = __DIR__ . '/../database/database.sqlite';
    $pdo = new PDO('sqlite:'.$db);
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    return $stmt->fetchColumn();
}

// 1) Login as waiter
$waiterCookie = $cookieDir . '/waiter.cookie';
@unlink($waiterCookie);
list($loginHtml, $info) = http_get('/login', $waiterCookie);
$token = extract_csrf($loginHtml);
if (!$token) { echo "ERROR: could not find CSRF token on /login\n"; exit(1); }

list($resp, $info) = http_post('/login', ['_token' => $token, 'email' => 'mesero@example.com', 'password' => 'password'], $waiterCookie);
echo "Login waiter HTTP status: " . ($info['http_code'] ?? 'N/A') . "\n";

// 2) GET create page to fetch token
list($createHtml, $info) = http_get('/staff/orders/create', $waiterCookie);
$token = extract_csrf($createHtml);
if (!$token) { echo "ERROR: could not find CSRF token on create page\n"; exit(1); }

// 3) POST order
$postData = [
    '_token' => $token,
    'items[0][itemID]' => $itemID,
    'items[0][quantity]' => 2,
    'items[0][comment]' => 'Smoke test'
];
list($postResp, $info) = http_post('/staff/orders', $postData, $waiterCookie);
echo "Create order HTTP status: " . ($info['http_code'] ?? 'N/A') . "\n";

// inspect DB for created order by mesero
$waiterId = user_id_by_email('mesero@example.com');
$db = __DIR__ . '/../database/database.sqlite';
$pdo = new PDO('sqlite:'.$db);
$stmt = $pdo->prepare('SELECT orderID, status, total, created_by FROM tbl_order WHERE created_by = :uid ORDER BY orderID DESC LIMIT 1');
$stmt->execute([':uid' => $waiterId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo "ERROR: no order found for waiter (created_by={$waiterId})\n";
    // dump recent orders for debugging
    $all = $pdo->query('SELECT * FROM tbl_order ORDER BY orderID DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    echo "Recent orders:\n" . json_encode($all, JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

echo "Order created: " . json_encode($order) . "\n";
$orderId = $order['orderID'];

// check order details
$stmt = $pdo->prepare('SELECT * FROM tbl_orderdetail WHERE orderID = :oid');
$stmt->execute([':oid' => $orderId]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Order details: " . json_encode($details) . "\n";

// 4) Login as chef and change status
$chefCookie = $cookieDir . '/chef.cookie';
@unlink($chefCookie);
list($loginHtml, $info) = http_get('/login', $chefCookie);
$token = extract_csrf($loginHtml);
list($resp, $info) = http_post('/login', ['_token' => $token, 'email' => 'cocina@example.com', 'password' => 'password'], $chefCookie);
echo "Login chef HTTP status: " . ($info['http_code'] ?? 'N/A') . "\n";

// fetch a page to get token (use kitchen or show)
list($kHtml, $info) = http_get('/staff/kitchen', $chefCookie);
$token = extract_csrf($kHtml) ?: extract_csrf($createHtml);
if (!$token) { echo "WARNING: could not find CSRF token for chef; trying order show page\n"; list($sHtml,$inf)=http_get('/staff/orders/'.$orderId, $chefCookie); $token=extract_csrf($sHtml); }
if (!$token) echo "Chef CSRF token missing\n";

list($res, $info) = http_post('/staff/orders/'.$orderId.'/status', ['_token' => $token, 'status' => 'ready'], $chefCookie);
echo "Change status HTTP status: " . ($info['http_code'] ?? 'N/A') . "\n";

// verify attended_by
$stmt = $pdo->prepare('SELECT attended_by, status FROM tbl_order WHERE orderID = :oid');
$stmt->execute([':oid' => $orderId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "After status change: " . json_encode($row) . "\n";

$chefId = user_id_by_email('cocina@example.com');
if ($row['attended_by'] == $chefId) {
    echo "SUCCESS: chef attended_by updated to {$chefId}\n";
} else {
    echo "FAIL: attended_by is {$row['attended_by']} expected {$chefId}\n";
}

echo "Smoke test finished.\n";
