<?php
session_start();

// Verifica se tem login, se não, manda login.php
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

require 'conexao.php'; 

// classificação da qualidade
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
");
$stmt->execute();
$result = $stmt->get_result();

$avaliacoes = [];
$img_path = 'img/';  // Pasta onde as imagens estão localizadas
$default_image = 'no_image.png';  // Caminho da imagem padrão (isso se n tiver imagem) "no_image.png"

// Loop que armazena todas as avaliações na variável $avaliacoes
while ($row = $result->fetch_assoc()) {
    // Caminho da imagem associada
    $imagem_caminho = $img_path . $row['AVACLIENTE'] . '_' . $row['AVAPRODUTO'] . '.jpg';
    
    // Verifica se a imagem existe, se não, usa a imagem padrão (caso sem imagem)
    $avaliacoes[] = [
        'usuario' => $row['USRNOME'],
        'produto' => $row['PRDNOME'],
        'nota' => $row['AVANOTA'],
        'comentario' => $row['AVACOMENTARIOS'],
        'qualidade_produto' => $row['qualidade_produto'],
        'imagem' => file_exists($imagem_caminho) ? $imagem_caminho : $default_image
    ];
}

$stmt->close();
mysqli_close($oCon);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações</title>
</head>
<body>
    <h2>Avaliações dos Produtos</h2>
    
    <?php if (count($avaliacoes) > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Produto</th>
                    <th>Nota</th>
                    <th>Comentário</th>
                    <th>Qualidade do Produto</th>
                    <th>Imagem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($avaliacoes as $avaliacao): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($avaliacao['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($avaliacao['produto']); ?></td>
                        <td><?php echo str_repeat('★', $avaliacao['nota']); ?></td>
                        <td><?php echo htmlspecialchars($avaliacao['comentario']); ?></td>
                        <td><?php echo htmlspecialchars($avaliacao['qualidade_produto']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($avaliacao['imagem']); ?>" alt="Imagem do produto" width="100"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhuma avaliação encontrada.</p>
    <?php endif; ?>

    <br>
    <a href="index.htm">
        <button>Continuar avaliando</button>
    </a>

</body>
</html>
