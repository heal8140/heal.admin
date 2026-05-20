<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 接続情報
$servername = "mysql322.phy.lolipop.lan";
$username   = "LAA1491315";
$password   = "test";
$dbname     = "LAA1491315-test";

// リクエスト受信
$json   = file_get_contents('php://input');
$data   = json_decode($json, true);
$customerId = $data['customer_id'];
$type       = $data['type'];
$content    = $data['content'];
$quantity   = intval($data['quantity']);
$usedAt     = $data['used_at'];

// DB接続
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  echo json_encode(['error' => 'DB接続失敗: ' . $conn->connect_error]);
  exit;
}

$conn->autocommit(true);

// ✅ サブクエリをネストで安全に構成
$sql = "
UPDATE `チケット管理`
SET 
  使用済み枚数 = 使用済み枚数 + ?,
  残数        = 残数 - ?,
  前回使用日   = ?,
  前回使用数   = ?
WHERE 顧客番号 = ?
  AND 種類     = ?
  AND 内容     = ?
  AND 前回使用日 = (
    SELECT 最新日 FROM (
      SELECT MAX(前回使用日) AS 最新日
      FROM `チケット管理`
      WHERE 顧客番号 = ? AND 種類 = ? AND 内容 = ?
    ) AS temp
)
";

// ✅ 正しくバインドして実行
$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(['error' => 'prepare失敗: ' . $conn->error]);
  exit;
}

$stmt->bind_param(
  "iisisissss",  // ← 修正：最後の 's' を追加（全部で10文字）
  $quantity,           // INT → i
  $quantity,           // INT → i
  $usedAt,             // STR → s
  $quantity,           // INT → i
  $customerId,         // INT → i
  $type,               // STR → s
  $content,            // STR → s
  $customerId,         // INT → i
  $type,               // STR → s
  $content             // STR → s ← 最後が漏れていた
);

if (!$stmt->execute()) {
  echo json_encode(['error' => 'execute失敗: ' . $stmt->error]);
  $stmt->close();
  $conn->close();
  exit;
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success']);
