<?php
$oCon = mysqli_connect('localhost', 'root', '', 'PRJ2DSB');
// conexão com o banco certo -> $oCon = mysqli_connect('localhost', 'DesenvolvedorB', 'B1scoito!', 'PRJ2DSB');

if (!$oCon) {
    die("Erro de conexão: " . mysqli_connect_error());
}
?>
