<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Responde a requisições OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'conexao.php';

// Verifica se a solicitação é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "error" => true,
        "message" => "Método não permitido. Use POST."
    ]);
    exit();
}

// Obtém os dados enviados no corpo da solicitação
$dados = json_decode(file_get_contents('php://input'), true);

// Verifica se o produtoId foi enviado
if (!isset($dados['produtoId'])) {
    echo json_encode([
        "error" => true,
        "message" => "O parâmetro 'produtoId' é obrigatório no corpo da solicitação."
    ]);
    exit();
}

// Sanitiza o ID do produto
$produtoId = intval($dados['produtoId']);

// Consulta as avaliações para o produto específico
$stmt = $oCon->prepare("
    SELECT a.AVACLIENTE, a.AVAPRODUTO, a.AVANOTA, a.AVACOMENTARIOS, p.PRDNOME, u.USRNOME,
           CASE
               WHEN a.AVANOTA BETWEEN 1 AND 2 THEN 'Péssima'
               WHEN a.AVANOTA = 3 THEN 'Média'
               WHEN a.AVANOTA BETWEEN 4 AND 5 THEN 'Ótima'
               ELSE 'Indefinida'
           END AS qualidade_produto
    FROM avaliacoes a
    JOIN produtos p ON a.AVAPRODUTO = p.PRDID
    JOIN usuarios u ON a.AVACLIENTE = u.USRID
    WHERE p.PRDID = ?
");
$stmt->bind_param('i', $produtoId);
$stmt->execute();
$result = $stmt->get_result();

$avaliacoes = [];
$img_path = 'img/';  // Pasta onde as imagens estão localizadas
$default_image = 'no_image.png';  // Caminho da imagem padrão

// Loop que armazena todas as avaliações na variável $avaliacoes
while ($row = $result->fetch_assoc()) {
    // Caminho da imagem associada
    $imagem_caminho = $img_path . $row['AVACLIENTE'] . '_' . $row['AVAPRODUTO'] . '.jpg';
    
    // Verifica se a imagem existe, se não, usa a imagem padrão
    $avaliacoes[] = [
        'usuario' => $row['USRNOME'],
        'produto' => $row['PRDNOME'],
        'nota' => $row['AVANOTA'],
        'comentario' => $row['AVACOMENTARIOS'],
        'qualidade_produto' => $row['qualidade_produto'],
        'imagem' => file_exists($imagem_caminho) ? $imagem_caminho : $default_image
    ];
}

// Verifica se há avaliações encontradas
if (empty($avaliacoes)) {
    echo json_encode([
        "error" => true,
        "message" => "Nenhuma avaliação encontrada para o produto especificado."
    ]);
} else {
    echo json_encode([
        "error" => false,
        "avaliacoes" => $avaliacoes
    ]);
}

$stmt->close();
mysqli_close($oCon);
?>