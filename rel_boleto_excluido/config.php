<?php

// Conexão com o Banco de Dados
$link = mysqli_connect("localhost", "root", "vertrigo", 'mkradius');

// Verificar a conexão
if (!$link) {
    die("Falha na conexão: " . mysqli_connect_error());
}

// Verificar se a sessão MKA_Usuario está vazia
$usuario_logado = isset($_SESSION['MKA_Usuario']) ? $_SESSION['MKA_Usuario'] : $_SESSION['MM_Usuario'];

// Fix MK-AUTH versões antigas
if (isset($_SESSION['MM_Usuario'])) {
    echo '<script src="../../scripts/vue.js"></script>';
}

$permissao = "perm_relFat";

// Verificar a permissão no banco de dados
$query_permissao = mysqli_query($link, "SELECT usuario FROM sis_perm WHERE nome LIKE '$permissao' AND usuario LIKE '$usuario_logado' AND permissao LIKE 'sim'");

if ($query_permissao) {
    $liberar_permissao = mysqli_num_rows($query_permissao);
    if ($liberar_permissao >= 1) {
        //echo "Acesso Liberado!"; // TUDO OK.
        $acesso_permitido = true;
    } else {
        //echo "Acesso Negado!";
        $acesso_permitido = false;
    }
} else {
    echo "Erro na consulta de permissão: " . mysqli_error($link);
    $acesso_permitido = false;
}

// Fix for MKAUTH 22.02
$ext_mk = (file_exists("../../index.hhvm")) ? '.hhvm' : '.php';

// Não feche a conexão aqui, será fechada no final do script index.php

?>
