<?php
// Criador de conexão
function dbConexao() {
     $oServidor = "localhost";
    $oUsuario = "DesenvolvedorB";
    $oSenha = "B1scoito!";
    $oBanco = "PRJ2DSB";

    $oCon = new mysqli($oServidor, $oUsuario, $oSenha, $oBanco);

    // Checagem
    if ($oCon->connect_error) die("Conexão falhou: " . $oCon->connect_error);
    
    return $oCon;
}
?>

