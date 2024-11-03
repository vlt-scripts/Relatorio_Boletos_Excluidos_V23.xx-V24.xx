<?php
// INCLUE FUNCOES DE ADDONS -----------------------------------------------------------------------
include('addons.class.php');

// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------
session_name('mka');
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['MKA_Logado'])) exit('Acesso negado... <a href="/admin/">Fazer Login</a>');
// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------

// Assuming $Manifest is defined somewhere before this code
$manifestTitle = isset($Manifest->{'name'}) ? $Manifest->{'name'} : '';
$manifestVersion = isset($Manifest->{'version'}) ? $Manifest->{'version'} : '';

// Processar a busca se os parâmetros estiverem presentes na URL
if (isset($_GET['search']) && isset($_GET['startDate']) && isset($_GET['endDate'])) {
    // Realize aqui o processamento da busca e exiba os resultados
    // Por exemplo:
    $search = $_GET['search'];
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $searchType = $_GET['searchType'] ?? 'all'; // Tipo de registro selecionado
} else {
    // Se os parâmetros de busca não estiverem presentes na URL, redirecione para a mesma página
    // com parâmetros de busca padrão (busca vazia e datas atuais)
    header("Location: {$_SERVER['PHP_SELF']}?search=&startDate=" . date('Y-m-d') . "&endDate=" . date('Y-m-d', strtotime('+1 day')) . "&searchType=all"); // Tipo de registro padrão é "todos"
    exit();
}

?>

<!DOCTYPE html>
<?php
if (isset($_SESSION['MM_Usuario'])) {
    echo '<html lang="pt-BR">'; // Fix versão antiga MK-AUTH
} else {
    echo '<html lang="pt-BR" class="has-navbar-fixed-top">';
}
?>
<html lang="pt-BR">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <title>MK - AUTH :: <?php echo $Manifest->{'name'} . " - V " . $Manifest->{'version'};  ?></title>

    <link href="../../estilos/mk-auth.css" rel="stylesheet" type="text/css" />
    <link href="../../estilos/font-awesome.css" rel="stylesheet" type="text/css" />

    <script src="../../scripts/jquery.js"></script>
    <script src="../../scripts/mk-auth.js"></script>
    <link href="../../estilos/bi-icons.css" rel="stylesheet" type="text/css" />
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="css/css.css" rel="stylesheet" type="text/css" />

    <style type="text/css">
        /* Estilos CSS personalizados */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #333;
        }

        form,
        .table-container,
        .client-count-container {
            width: 100%;
            margin: 0 auto;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="submit"],
        .clear-button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
        }

        input[type="submit"] {
            background-color: #4caf50;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .clear-button {
            background-color: #e74c3c;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .clear-button:hover {
            background-color: #c0392b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        table th,
        table td {
            padding: 1px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #0d6cea;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        h1 {
            color: #4caf50;
        }

        .client-count-container {
            text-align: center;
            margin-top: 10px;
        }

        .client-count {
            color: #4caf50;
            font-weight: bold;
        }

        .client-count.blue {
            color: #2196F3;
        }

        .nome_cliente a {
            color: blue;
            text-decoration: none;
            font-weight: bold;
        }

        .nome_cliente a:hover {
            text-decoration: underline;
        }

        .nome_cliente td {
            text-align: center;
        }

        .nome_cliente:nth-child(odd) {
            background-color: #FFFF99;
        }

        /* Estilo para ressaltar letras */
        .highlighted {
            color: #f44336; /* Cor vermelha */
            font-weight: bold;
        }
        .resultado-cell {
            border: 1px solid #ddd;
            padding: 3px; /* Altere este valor conforme necessário */
            text-align: center;
        }
    </style>

</head>

<body>
    <?php include('../../topo.php'); ?>

    <nav class="breadcrumb has-bullet-separator is-centered" aria-label="breadcrumbs">
        <ul>
            <li><a href="#"> ADDON</a></li>
            <li class="is-active">
                <a href="#" aria-current="page"> <?php echo htmlspecialchars($manifestTitle . " - V " . $manifestVersion); ?> </a>
            </li>
        </ul>
    </nav>

    <?php include('config.php'); ?>

    <?php
    if ($acesso_permitido) {
        // Formulário Atualizado com Funcionalidade de Busca
    ?>
<form id="searchForm" method="GET">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div style="width: 80%; margin-right: 10px;">
            <label for="search" style="font-weight: bold; margin-bottom: 5px;">Buscar Cliente:</label>
            <input type="text" id="search" name="search" placeholder="Digite o Login ou Usuário" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc;">
        </div>
        <div style="flex: 1; margin-right: 10px;">
            <label for="startDate" style="font-weight: bold; margin-bottom: 5px; display: block;">Data de Início:</label>
            <input type="date" id="startDate" name="startDate" value="<?php echo isset($_GET['startDate']) ? htmlspecialchars($_GET['startDate']) : date('Y-m-d'); ?>" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc;">
        </div>
        <div style="flex: 1; margin-right: 10px;">
            <label for="endDate" style="font-weight: bold; margin-bottom: 5px; display: block;">Data de Fim:</label>
            <input type="date" id="endDate" name="endDate" value="<?php echo isset($_GET['endDate']) && $_GET['endDate'] !== '' ? htmlspecialchars($_GET['endDate']) : date('Y-m-d', strtotime('+1 day')); ?>" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc;">
        </div>
        <div style="width: 15%; margin-right: 10px;">
            <label for="searchType" style="font-weight: bold; margin-bottom: 5px;">Tipo de Registro:</label>
        <select id="searchType" name="searchType" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc;">
            <option value="all" <?php echo ($searchType == 'all') ? 'selected' : ''; ?>>Todos</option>
            <option value="titulos" <?php echo ($searchType == 'titulos') ? 'selected' : ''; ?>>Títulos</option>
            <option value="parcelas" <?php echo ($searchType == 'parcelas') ? 'selected' : ''; ?>>Parcelas</option>
            <option value="carne" <?php echo ($searchType == 'carne') ? 'selected' : ''; ?>>Carne</option>
            <option value="cancelou" <?php echo ($searchType == 'cancelou') ? 'selected' : ''; ?>>Cancelou</option>
        </select>

        </div>
        <div style="flex: 1;">
        <input type="submit" value="Buscar" style="width: 100%; padding: 10px; border: 1px solid #4caf50; background-color: #4caf50; color: white; font-weight: bold; cursor: pointer; border-radius: 5px;">
        </div>
        <div style="flex: 1; margin-left: 10px;">
        <button type="button" onclick="clearSearch()" class="clear-button" style="width: 100%; padding: 10px; border: 1px solid #e74c3c; background-color: #e74c3c; color: white; font-weight: bold; cursor: pointer; border-radius: 5px;">Limpar</button>
        </div>
    </div>
</form>
<script>
    $(document).ready(function() {
        $('.login-clickable').click(function() {
            var login = $(this).text();
            $('#search').val(login); // Preenche o campo de busca com o login clicado
            $('#searchForm').submit(); // Submete o formulário de pesquisa
        });
    });
</script>
<script>
function showFullText(element) {
  var text = element.textContent || element.innerText;
  element.setAttribute('title', text);
}

function hideFullText(element) {
  element.removeAttribute('title');
}
</script>

<script type="text/javascript">
    function clearSearch() {
        document.getElementById('search').value = '';
        document.getElementById('startDate').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('endDate').value = '<?php echo date('Y-m-d', strtotime('+1 day')); ?>';
        document.getElementById('searchType').value = 'all'; // Definir Tipo de Registro de volta para "todos"
        document.getElementById('searchForm').submit();
    }
</script>

    <!-- Tabela: Registros, Data, Tipo e Login -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
				    
                    <th style='text-align: center;'>Login</th>
                    <th style='text-align: center;'>Data Exclusão</th>
                    <th style='text-align: center;'>Usuário</th>
                    <th style='text-align: center; width: 400px;'>Registro</th>
                    <th style='text-align: center;'>Valor</th>
					<th style='text-align: center;'>ID</th>
                </tr>
            </thead>
            <tbody>
<?php
// Consulta SQL para obter os registros, data, tipo, login e valor do boleto
$query = "(SELECT DISTINCT
            central.login, 
            central.data, 
            'usuario' as tipo, 
            central.registro,
            admin.login as admin_login,
            central.data as order_date
          FROM 
            sis_logs as central
          LEFT JOIN 
            sis_logs as admin ON central.data = admin.data AND admin.tipo = 'admin'
          WHERE 
            central.tipo = 'central' 
            AND (
                  (
                    (central.registro LIKE '%deletou parcela%' OR central.registro LIKE '%deletou o carne%') 
                    OR (central.registro LIKE '%cancelou título%') 
                    OR (central.registro LIKE '%deletou título%' AND central.registro LIKE '%pelo motivo:%')
                  ) 
                  AND (admin.login LIKE '%$search%' OR central.login LIKE '%$search%' OR central.registro LIKE '%$search%')
                ";


// Adiciona a filtragem por datas de início e fim
if (!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
    $startDate = mysqli_real_escape_string($link, $_GET['startDate']);
    $endDate = mysqli_real_escape_string($link, $_GET['endDate']);

    // Convertendo as datas para o formato MySQL
    $startDateMySQL = date('Y-m-d', strtotime($startDate));
    $endDateMySQL = date('Y-m-d', strtotime($endDate));

    $query .= " AND DATE(STR_TO_DATE(central.data, '%d/%m/%Y %H:%i:%s')) BETWEEN '$startDateMySQL' AND '$endDateMySQL'";
}

// Adiciona a filtragem por Tipo de Registro se fornecido
if (isset($_GET['searchType']) && in_array($_GET['searchType'], ['titulos', 'parcelas', 'carne', 'cancelou'])) {
    switch ($_GET['searchType']) {
        case 'titulos':
            $query .= " AND central.registro LIKE '%deletou título%'";
            break;
        case 'parcelas':
            $query .= " AND central.registro LIKE '%deletou parcela%'";
            break;
        case 'carne':
            $query .= " AND central.registro LIKE '%deletou o carne%'";
            break;
        case 'cancelou':
            $query .= " AND central.registro LIKE '%cancelou título%'";
            break;
    }
}


$query .= "))";

// Verifica se a tabela sis_ativ existe no banco de dados
$checkQuery = "SHOW TABLES LIKE 'sis_ativ'";
$checkResult = mysqli_query($link, $checkQuery);

if ($checkResult && mysqli_num_rows($checkResult) > 0) {
// A tabela sis_ativ existe, então você pode adicionar a consulta relacionada a ela
$query .= " UNION DISTINCT
            (SELECT DISTINCT
            central.login, 
            central.data, 
            'usuario' as tipo, 
            central.registro,
            admin.login as admin_login,
            central.data as order_date
          FROM 
            sis_ativ as central
          LEFT JOIN 
            sis_ativ as admin ON central.data = admin.data AND admin.tipo = 'admin'
          WHERE 
            central.tipo = 'central' 
            AND (
                  (
                    (central.registro LIKE '%deletou parcela%' OR central.registro LIKE '%deletou o carne%') 
                    OR (central.registro LIKE '%deletou título%' AND central.registro LIKE '%pelo motivo:%')
                    OR (central.registro LIKE '%cancelou título%')
                  ) 
                  AND (admin.login LIKE '%$search%' OR central.login LIKE '%$search%' OR central.registro LIKE '%$search%')
                ";


    // Adiciona a filtragem por datas se forem fornecidas
    if (!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
        $startDate = mysqli_real_escape_string($link, $_GET['startDate']);
        $endDate = mysqli_real_escape_string($link, $_GET['endDate']);
        $query .= " AND DATE(central.data) BETWEEN '$startDate' AND '$endDate'";
    }

// Adiciona a filtragem por Tipo de Registro se fornecido
if (isset($_GET['searchType']) && in_array($_GET['searchType'], ['titulos', 'parcelas', 'carne', 'cancelou'])) {
    switch ($_GET['searchType']) {
        case 'titulos':
            $query .= " AND central.registro LIKE '%deletou título%'";
            break;
        case 'parcelas':
            $query .= " AND central.registro LIKE '%deletou parcela%'";
            break;
        case 'carne':
            $query .= " AND central.registro LIKE '%deletou o carne%'";
            break;
        case 'cancelou':
            $query .= " AND central.registro LIKE '%cancelou título%'";
            break;
    }
}


    $query .= "))";
} else {
    // A tabela sis_ativ não existe, nesta Versão V23.xx so na V24.xx
   //echo "<div class='client-count-container'><p class='client-count blue'>A tabela sis_ativ não existe nesta versão.</p></div>";
 }

// Contador de Total de Boletos Excluídos
$total_boletos_excluidos = 0;

// Ordena os resultados pela data mais recente
$query .= " ORDER BY order_date DESC";

$result = mysqli_query($link, $query);

if ($result) {
    $rowNumber = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        // Adiciona a classe 'nome_cliente' e 'highlight' (para linhas ímpares) alternadamente
        $rowNumber++;
        $nomeClienteClass = ($rowNumber % 2 == 0) ? 'nome_cliente' : 'nome_cliente highlight';

        // Extrai o ID do registro usando expressão regular
        preg_match('/(deletou|cancelou) (parcela|o carne|titulo) (\w+)/i', $row['registro'], $matches);

        $id = isset($matches[3]) ? $matches[3] : '';

        // Consulta SQL para obter a data de exclusão do sis_lanc relacionada ao ID extraído
        $datadel = '';
        if (!empty($id)) {
            $datadelQuery = "SELECT datadel FROM sis_lanc WHERE id = $id";
            $datadelResult = mysqli_query($link, $datadelQuery);
            if ($datadelResult && mysqli_num_rows($datadelResult) > 0) {
                $datadelRow = mysqli_fetch_assoc($datadelResult);
                $datadel = $datadelRow['datadel'];
            }
        }

        // Consulta SQL para obter o valor do boleto relacionado ao ID extraído
        $valor = '';
        if (!empty($id)) {
            $valorQuery = "SELECT valor FROM sis_lanc WHERE id = $id";
            $valorResult = mysqli_query($link, $valorQuery);
            if ($valorResult && mysqli_num_rows($valorResult) > 0) {
                $valorRow = mysqli_fetch_assoc($valorResult);
                $valor = $valorRow['valor'];
                // Incrementa o contador de Total de Boletos Excluídos
                //$total_boletos_excluidos++;
            }
        }

        // Consulta SQL para obter o valor do carne relacionado ao código_carne extraído
        $valor_carne = '';
        if (!empty($id)) {
            $valorCarneQuery = "SELECT valor FROM sis_lanc WHERE codigo_carne = '$id'";
            $valorCarneResult = mysqli_query($link, $valorCarneQuery);
            if ($valorCarneResult && mysqli_num_rows($valorCarneResult) > 0) {
                $valorCarneRow = mysqli_fetch_assoc($valorCarneResult);
                $valor_carne = $valorCarneRow['valor'];
                // Incrementa o contador de Total de Boletos Excluídos
                //$total_boletos_excluidos++;
            }
        }

        echo "<tr class='$nomeClienteClass'>";
        // Login
        echo "<td class='resultado-cell'><a href=\"javascript:void(0);\" onclick=\"searchByTipoCob('".$row['login']."')\">" . "<img src='img/icon_cliente.png' alt='Cliente Icon' style='width: 20px; height: 20px; float: left; margin-right: 5px;'>" . "<span class='login-clickable'>" . $row['login'] . "</span>" . "</a></td>";

        // Data Exclusão
        // Primeiro, convertemos a data no formato "d/m/Y H:i:s" para um formato aceito pelo strtotime() (Y-m-d H:i:s).
        $order_date_timestamp = strtotime(str_replace('/', '-', $row['order_date']));

        // Agora, formatamos a data conforme necessário.
        $order_date_formatted = $order_date_timestamp ? date('d/m/Y H:i:s', $order_date_timestamp) : '';

        // Finalmente, exibimos a data na célula da tabela.
        echo "<td class='resultado-cell'>" . ($row['order_date'] ? "<a style='text-align: center; text-decoration: none; cursor: default; color: green;'>" . $order_date_formatted . "</a>" : '') . "</td>";

        // Usuário
        echo "<td class='resultado-cell'><a href=\"javascript:void(0);\" onclick=\"searchByTipoCob('".($row['admin_login'] ? $row['admin_login'] : $row['login'])."')\">" . "<span class='login-clickable'>" . ($row['admin_login'] ? $row['admin_login'] : $row['login']) . "</span>" . "</a></td>";

        // Registro
        echo "<td class='resultado-cell' style='max-width: 150px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'><a href='#' style='text-decoration: none; cursor: default; color: #f35812;' onmouseover='showFullText(this)' onmouseout='hideFullText(this)'>" . $row['registro'] . "</a></td>";
        
		// Incrementa o contador de Total de Boletos Excluídos
        $total_boletos_excluidos++;
        
		// Valor
        echo "<td class='resultado-cell' style='width: 125px;'>"; // Ajustando a largura para 130 pixels
        echo $valor !== '' ? "<img src='img/icon_boleto.png' alt='Icon' style='width: 20px; height: 20px; float: left; margin-right: 5px;'>" . "<a href='#' style='text-decoration: none; cursor: default; color: #1c0dea;'>R$ $valor</a>" : '';
        echo $valor !== '' && $valor_carne !== '' ? "<br>" : '';
        echo $valor_carne !== '' ? "<img src='img/icon_boleto.png' alt='Icon' style='width: 20px; height: 20px; float: left; margin-right: 5px;'>" . "<a href='#' style='text-decoration: none; cursor: default; color: #1c0dea;'>R$ $valor_carne</a>" : '';
        echo "</td>";

        // ID
        echo "<td class='resultado-cell' style='position: relative;'><a href=\"javascript:void(0);\" onclick=\"searchById('".$id."')\">" . "<img src='img/digital.png' alt='Ícone' style='width: 20px; height: 20px; position: absolute; left: 0; top: 50%; transform: translateY(-50%);'>" . "<span class='login-clickable' style='color: #007ff7; margin-left: 25px;'>" . $id . "</span>" . "</a></td>";
        echo "</tr>";
    }
}       // Total de Boletos Excluídos
        echo "<div class='client-count-container'><p class='client-count blue'>Total de Boletos Excluídos: $total_boletos_excluidos</p></div>";
?>

            </tbody>
        </table>
    </div>
    <?php
    } else {
        echo "Acesso não permitido!";
    }
    ?>

    <?php include('../../baixo.php'); ?>

    <script src="../../menu.js.php"></script>
    <?php include('../../rodape.php'); ?>
</body>



</html>
