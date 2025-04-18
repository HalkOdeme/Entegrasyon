<?php
$host = "127.0.0.1";
$db = "testphp";
$user = "root";
$pass = ""; // Şifre genellikle boş olur, eğer farklıysa buraya yaz

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Hata yakalama modu aktif
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
