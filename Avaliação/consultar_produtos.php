<?php
require 'conexao.php';

header('Content-Type: application/json');

// Consultar todos os produtos (ID e nome)
$stmt = $oCon->prepare("SELECT PRDID, PRDNOME FROM produtos WHERE PRDATIVO = 1"); // Filtra para pegar apenas os produtos ativos
$stmt->execute();
$result = $stmt->get_result();

$produtos = [];

// Adiciona os produtos ao array
while ($row = $result->fetch_assoc()) {
    $produtos[] = [
        'id' => $row['PRDID'],  // ID do produto
        'nome' => $row['PRDNOME'] // Nome do produto
    ];
}

echo json_encode($produtos);

$stmt->close();
mysqli_close($oCon);
?>
