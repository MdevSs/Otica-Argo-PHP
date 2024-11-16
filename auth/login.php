<?php
header('Access-Control-Allow-Origin: *'); // Permitir acesso de qualquer origem
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Métodos permitidos
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Cabeçalhos permitidos
header('Access-Control-Allow-Credentials: true'); // Permitir credenciais

// Responde a requisições OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inicia a sessão
session_start();

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Resposta padrão
    $response = [
        "autenticado" => false,
        "sessionId" => null,
        "userDados" => null
    ];

    // Lê o JSON do corpo da requisição
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (isset($inputData['txtEmail']) && isset($inputData['txtSenha'])) {
        // Dados do banco de dados
        $oServidor = "localhost";
        $oUsuario = "root";
        $oSenha = "";
        $oBanco = "prj2dsb";

        // Conecta ao banco de dados
        $oCon = new mysqli($oServidor, $oUsuario, $oSenha, $oBanco);

        // Verifica a conexão
        if ($oCon->connect_error) {
            $response['error'] = "Falha na conexão: " . $oCon->connect_error;
            echo json_encode($response);
            exit();
        }

        // Dados do formulário
        $email = $inputData['txtEmail'];
        $senha = md5($inputData['txtSenha']); // Convertendo para MD5 (não recomendado, use password_hash)

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

            // Busca os dados completos do usuário
            $selectStmt = $oCon->prepare("
                SELECT `USRID`, `USRNOME`, `USREMAIL`, `USRCPF`, `USRDTNASC`, `USRNIVELACESSO`, `USRBLOQUEADO`
                FROM `usuarios`
                WHERE USREMAIL = ?
            ");

            if ($selectStmt === false) {
                $response['error'] = "Erro na preparação da consulta para buscar os dados completos: " . $oCon->error;
            } else {
                $selectStmt->bind_param('s', $email);
                $selectStmt->execute();
                $result = $selectStmt->get_result();

                if ($result) {
                    $response['userDados'] = $result->fetch_assoc();
                } else {
                    $response['error'] = "Erro ao obter dados do usuário.";
                }
                $selectStmt->close(); // Fecha apenas se foi preparado corretamente
            }
        } else {
            $response['error'] = "Usuário não encontrado.";
        }

        // Fecha as conexões
        $stmt->close();
        $oCon->close();
    } else {
        $response['error'] = "Dados de email ou senha não fornecidos.";
    }

    // Retorna a resposta JSON
    echo json_encode($response);
    exit();
}
?>
