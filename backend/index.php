<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'conexao.php';

$id = rand(1, 999999); 
$nome = isset($_POST["nome"]) ? filter_var($_POST["nome"], FILTER_SANITIZE_STRING) : '';
$email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';
$comentario = isset($_POST["comentario"]) ? filter_var($_POST["comentario"], FILTER_SANITIZE_STRING) : '';

$query = "INSERT INTO mensagens(id, nome, email, comentario) VALUES (?, ?, ?, ?)";
$stmt = $link->prepare($query);

if ($stmt === FALSE) {
    error_log("Prepare failed: " . $link->error);
    http_response_code(500);
    echo "Erro ao preparar a query.";
    exit();
}

$stmt->bind_param("isss", $id, $nome, $email, $comentario);

if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    error_log("Error executing query: " . $stmt->error);
    http_response_code(500);
    echo "Error: " . $stmt->error;
}

$stmt->close();
$link->close();
?>