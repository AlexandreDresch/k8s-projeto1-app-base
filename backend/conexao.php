<?php
$servername = "mysql-service";
$username = "root";
$password = "Senha123";
$database = "meubanco";

$link = new mysqli($servername, $username, $password, $database);

if (mysqli_connect_errno()) {
    error_log("Connect failed: " . mysqli_connect_error());
    http_response_code(500);
    echo "Erro de conexão com o banco de dados.";
    exit();
}
?>