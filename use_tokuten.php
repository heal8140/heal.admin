<?php
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$input = json_decode(file_get_contents("php://input"), true);
$customer_id = intval($input["customer_id"] ?? 0);

$servername = "mysql322.phy.lolipop.lan";
$username = "LAA1491315";
$password = "test";
$dbname = "LAA1491315-test";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success"=>false, "error"=>"DB接続失敗"]);
    exit;
}

// 最新の日付の未使用データを1件取得
$sql = "SELECT 日付 FROM 紹介特典 
        WHERE 特典対象者 = $customer_id AND 特典使用状況 = '未使用'
        ORDER BY 日付 DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $date = $row["日付"];
    $update = "UPDATE 紹介特典 
               SET 特典使用状況 = '使用済' 
               WHERE 特典対象者 = $customer_id 
                 AND 日付 = '$date' 
                 AND 特典使用状況 = '未使用'
               LIMIT 1";
    if ($conn->query($update)) {
        echo json_encode(["success"=>true]);
    } else {
        echo json_encode(["success"=>false, "error"=>"更新失敗"]);
    }
} else {
    echo json_encode(["success"=>false, "error"=>"未使用データがありません"]);
}

$conn->close();
