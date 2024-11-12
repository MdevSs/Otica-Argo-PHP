<?php
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
header('Content-Type: application/json'); // Define o tipo de resposta como JSON
$response = [
    "autenticado" => false,
    "sessionId" => null,
    "userDados" => null
];

if (isset($_POST['txtEmail']) && isset($_POST['txtSenha'])) {

    // Conectar ao banco de dados
    $oServidor = "localhost";
    $oUsuario = "DesenvolvedorB";
    $oSenha = "B1scoito!";
    $oBanco = "PRJ2DSB";


    $oCon = new mysqli($oServidor, $oUsuario, $oSenha, $oBanco);

    // Verifica a conexão
    if ($oCon->connect_error) {
        $response['error'] = "Falha na conexão: " . $oCon->connect_error;
        echo json_encode($response);
        exit();
    }

    // Dados do formulário
    $email = $_POST['txtEmail'];
    $senha = md5($_POST['txtSenha']); //Convertendo para MD5

    // Prepara a consulta para evitar SQL Injection
    $stmt = $oCon->prepare("
        SELECT USREMAIL, USRNIVELACESSO 
        FROM usuarios
        WHERE USREMAIL = ? AND USRSENHA = ?
    ");

    // Verifica se a preparação deu certo
    if ($stmt === false) {
        $response['error'] = "Erro na preparação da consulta: " . $oCon->error;
        echo json_encode($response);
        exit();
    }

    // Executa a consulta
    $stmt->bind_param('ss', $email, $senha); // 'ss' corresponde a dois parâmetros de string
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se o usuário foi encontrado
    if ($oRow = $result->fetch_assoc()) {
        $_SESSION['USREMAIL'] = $oRow['USREMAIL'];
        $_SESSION['USRNIVELACESSO'] = $oRow['USRNIVELACESSO'];
        
        // Define o session ID e prepara a resposta JSON
        $response['autenticado'] = true;
        $response['sessionId'] = session_id();
        $selectStmt = $oCon->prepare("SELECT `USRID`, `USRNOME`, `USREMAIL`, `USRCPF`, `USRDTNASC`, `USRNIVELACESSO`, `USRBLOQUEADO` FROM `usuarios` WHERE USREMAIL = ?");
        $selectStmt->bind_param('s', $email);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        $response['userDados'] = $result->fetch_assoc();
   
  }
  else {
        $response['error'] = "Usuário não encontrado.";
    }
    // Fecha a conexão
    $stmt->close();
    $oCon->close();
}

// Retorna a resposta JSON
echo json_encode($response);
exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
<form action="" method="POST">
            <label>
                <span>Email</span>
                <input type="email" name="txtEmail" required>
            </label>

            <label>
                <span>Senha</span>
                <input type="password" name="txtSenha" required>
            </label>

            <button type="submit"><strong>Validar</strong></button>
</form>
</body>
</html>
