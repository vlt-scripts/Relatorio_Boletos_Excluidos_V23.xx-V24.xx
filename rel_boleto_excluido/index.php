<?php
// INCLUE FUNCOES DE ADDONS -----------------------------------------------------------------------
include('addons.class.php');

// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------
session_name('mka');
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['MKA_Logado'])) exit('Acesso negado... <a href="/admin/">Fazer Login</a>');
// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------

$manifestTitle = isset($Manifest->{'name'}) ? $Manifest->{'name'} : '';
$manifestVersion = isset($Manifest->{'version'}) ? $Manifest->{'version'} : '';

// Processar a busca se os parâmetros estiverem presentes na URL
if (isset($_GET['search']) && isset($_GET['startDate']) && isset($_GET['endDate'])) {
    $search = $_GET['search'];
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $searchType = $_GET['searchType'] ?? 'all';
    $searchStatus = $_GET['searchStatus'] ?? 'all'; // Novo filtro de status
} else {
    header("Location: {$_SERVER['PHP_SELF']}?search=&startDate=" . date('Y-m-d') . "&endDate=" . date('Y-m-d', strtotime('+1 day')) . "&searchType=all&searchStatus=all");
    exit();
}

?>

<!DOCTYPE html>
<?php
if (isset($_SESSION['MM_Usuario'])) {
    echo '<html lang="pt-BR">';
} else {
    echo '<html lang="pt-BR" class="has-navbar-fixed-top">';
}
?>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <title>MK - AUTH :: <?php echo $Manifest->{'name'} . " - V " . $Manifest->{'version'};  ?></title>

    <link href="../../estilos/mk-auth.css" rel="stylesheet" type="text/css" />
    <link href="../../estilos/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="../../estilos/bi-icons.css" rel="stylesheet" type="text/css" />

    <script src="../../scripts/jquery.js"></script>
    <script src="../../scripts/mk-auth.js"></script>

    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
            color: #2d3748;
            line-height: 1.5;
        }

        .container-wrapper {
            max-width: 0 auto;
            margin: 0 auto;
        }

        /* Header Stats - Inline */
        .stats-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
        }

        .stat-chip {
            background: white;
            border-radius: 20px;
            padding: 10px 42px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        .stat-chip:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .stat-chip.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .stat-chip.active .stat-chip-label,
        .stat-chip.active .stat-chip-value {
            color: white;
        }

        .stat-chip-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
        }

        .stat-chip-label {
            color: #718096;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-chip-value {
            color: #2d3748;
            font-size: 18px;
            font-weight: 700;
        }

        /* Search Card - Compact */
        .search-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }

        .search-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: #2d3748;
            font-size: 17px;
            font-weight: 600;
        }

        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 14px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 7px;
            font-size: 13px;
        }

        .form-group input,
        .form-group select {
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #f7fafc;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .button-group {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            flex: 1;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 1.5px solid #667eea;
        }

        .btn-secondary:hover {
            background: #f7fafc;
            transform: translateY(-1px);
        }

        /* Table - Modern & Clean */
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 14px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        table tbody tr:hover {
            background-color: #f8fafc;
        }

        table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        table tbody tr:nth-child(even):hover {
            background-color: #f8fafc;
        }

        table tbody td {
            padding: 12px 14px;
            text-align: center;
            vertical-align: middle;
            font-size: 14px;
            color: #4a5568;
        }

        table tbody td a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        table tbody td a:hover {
            color: #764ba2;
        }

        .icon-inline {
            width: 18px;
            height: 18px;
            vertical-align: middle;
            margin-right: 5px;
            opacity: 0.8;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .search-form {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }

            .stats-inline {
                flex-direction: column;
                align-items: stretch;
            }

            .stat-chip {
                justify-content: space-between;
            }

            .button-group {
                flex-direction: column;
            }
        }

        /* Melhor legibilidade */
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        ::selection {
            background: #667eea;
            color: white;
        }
    </style>
</head>

<body>
    <?php include('../../topo.php'); ?>

    <nav class="breadcrumb has-bullet-separator is-centered" aria-label="breadcrumbs">
        <ul>
            <li><a href="#">ADDON</a></li>
            <li class="is-active">
                <a href="#" aria-current="page"><?php echo htmlspecialchars($manifestTitle . " - V " . $manifestVersion); ?></a>
            </li>
        </ul>
    </nav>

    <?php include('config.php'); ?>

    <?php if ($acesso_permitido) { ?>
        
    <div class="container-wrapper">
        
        <!-- Stats Inline - Compacto -->
        <div class="stats-inline">
            <div class="stat-chip" onclick="filterByStatus('all')" id="chip-all">
                <div class="stat-chip-icon" style="background: linear-gradient(135deg, #ff6b6b, #ee5a6f);">
                    <i class="fa fa-trash"></i>
                </div>
                <div>
                    <div class="stat-chip-label">Excluídos</div>
                    <div class="stat-chip-value" id="total-excluidos">0</div>
                </div>
            </div>

            <div class="stat-chip" onclick="filterByStatus('cancelado')" id="chip-cancelado">
                <div class="stat-chip-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                    <i class="fa fa-ban"></i>
                </div>
                <div>
                    <div class="stat-chip-label">Cancelados</div>
                    <div class="stat-chip-value" id="total-cancelados">0</div>
                </div>
            </div>

            <div class="stat-chip" onclick="filterByStatus('vencido')" id="chip-vencido">
                <div class="stat-chip-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                    <i class="fa fa-clock-o"></i>
                </div>
                <div>
                    <div class="stat-chip-label">Vencido</div>
                    <div class="stat-chip-value" id="total-vencido">0</div>
                </div>
            </div>

            <div class="stat-chip" onclick="filterByStatus('aberto')" id="chip-aberto">
                <div class="stat-chip-icon" style="background: linear-gradient(135deg, #a8edea, #fed6e3);">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <div class="stat-chip-label">Aberto</div>
                    <div class="stat-chip-value" id="total-aberto">0</div>
                </div>
            </div>

            <!--<div class="stat-chip" onclick="filterByStatus('deletado')" id="chip-deletado">
                <div class="stat-chip-icon" style="background: linear-gradient(135deg, #ff9a9e, #fecfef);">
                    <i class="fa fa-times-circle"></i>
                </div>
                <div>
                    <div class="stat-chip-label">Deletado</div>
                    <div class="stat-chip-value" id="total-deletado">0</div>
                </div>
            </div>

            <div class="stat-chip" onclick="filterByStatus('outros')" id="chip-outros">
                <div class="stat-chip-icon" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
                    <i class="fa fa-question-circle"></i>
                </div>
                <div>
                    <div class="stat-chip-label">Outros</div>
                    <div class="stat-chip-value" id="total-outros">0</div>
                </div>
            </div>-->

            <div class="stat-chip" onclick="filterByStatus('periodo')" id="chip-periodo">
                <div class="stat-chip-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                    <i class="fa fa-calendar"></i>
                </div>
                <div>
                    <div class="stat-chip-label">Período</div>
                    <div class="stat-chip-value" id="periodo" style="font-size: 14px;">Hoje</div>
                </div>
            </div>
			            <div class="stat-chip" onclick="filterByStatus('valor')" id="chip-valor">
                <div class="stat-chip-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                    <i class="fa fa-money"></i>
                </div>
                <div>
                    <div class="stat-chip-label">Valor Total</div>
                    <div class="stat-chip-value" id="valor-total">R$ 0,00</div>
                </div>
            </div>
        </div>

        <!-- Formulário de Busca -->
        <div class="search-card">
            <div class="search-header">
                <i class="fa fa-search"></i>
                <span>Filtros de Busca</span>
            </div>
            <form id="searchForm" method="GET">
                <div class="search-form">
                    <div class="form-group">
                        <label for="search">Cliente</label>
                        <input type="text" id="search" name="search" placeholder="Login ou Usuário" 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="startDate">Data Início</label>
                        <input type="date" id="startDate" name="startDate" 
                               value="<?php echo isset($_GET['startDate']) ? htmlspecialchars($_GET['startDate']) : date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="endDate">Data Fim</label>
                        <input type="date" id="endDate" name="endDate" 
                               value="<?php echo isset($_GET['endDate']) && $_GET['endDate'] !== '' ? htmlspecialchars($_GET['endDate']) : date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="searchType">Tipo</label>
                        <select id="searchType" name="searchType">
                            <option value="all" <?php echo ($searchType == 'all') ? 'selected' : ''; ?>>Todos</option>
                            <option value="titulos" <?php echo ($searchType == 'titulos') ? 'selected' : ''; ?>>Títulos</option>
                            <option value="parcelas" <?php echo ($searchType == 'parcelas') ? 'selected' : ''; ?>>Parcelas</option>
                            <option value="carne" <?php echo ($searchType == 'carne') ? 'selected' : ''; ?>>Carnê</option>
                            <option value="cancelou" <?php echo ($searchType == 'cancelou') ? 'selected' : ''; ?>>Cancelados</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" id="searchStatus" name="searchStatus" value="<?php echo htmlspecialchars($searchStatus); ?>">

                <div class="button-group" style="margin-top: 15px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                    <button type="button" onclick="clearSearch()" class="btn btn-secondary">
                        <i class="fa fa-eraser"></i> Limpar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabela de Resultados -->
        <div class="table-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Login</th>
                            <th>Data Exclusão</th>
                            <th>Usuário</th>
                            <th>Registro</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>ID</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
// Consulta SQL para obter os registros
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
                    (central.registro LIKE '%deletou parcela%' OR central.registro LIKE '%deletou o carne%' OR central.registro LIKE '%deletou carne%') 
                    OR (central.registro LIKE '%cancelou título%') 
                    OR (central.registro LIKE '%deletou título%' AND central.registro LIKE '%pelo motivo:%')
                  ) 
                  AND (admin.login LIKE '%$search%' OR central.login LIKE '%$search%' OR central.registro LIKE '%$search%')
                ";

if (!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
    $startDate = mysqli_real_escape_string($link, $_GET['startDate']);
    $endDate = mysqli_real_escape_string($link, $_GET['endDate']);
    $startDateMySQL = date('Y-m-d', strtotime($startDate));
    $endDateMySQL = date('Y-m-d', strtotime($endDate));
    $query .= " AND DATE(STR_TO_DATE(central.data, '%d/%m/%Y %H:%i:%s')) BETWEEN '$startDateMySQL' AND '$endDateMySQL'";
}

if (isset($_GET['searchType']) && in_array($_GET['searchType'], ['titulos', 'parcelas', 'carne', 'cancelou'])) {
    switch ($_GET['searchType']) {
        case 'titulos':
            $query .= " AND central.registro LIKE '%deletou título%'";
            break;
        case 'parcelas':
            $query .= " AND central.registro LIKE '%deletou parcela%'";
            break;
        case 'carne':
            $query .= " AND (central.registro LIKE '%deletou o carne%' OR central.registro LIKE '%deletou carne%')";
            break;
        case 'cancelou':
            $query .= " AND central.registro LIKE '%cancelou título%'";
            break;
    }
}

$query .= "))";

$checkQuery = "SHOW TABLES LIKE 'sis_ativ'";
$checkResult = mysqli_query($link, $checkQuery);

if ($checkResult && mysqli_num_rows($checkResult) > 0) {
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
                        (central.registro LIKE '%deletou parcela%' OR central.registro LIKE '%deletou o carne%' OR central.registro LIKE '%deletou carne%') 
                        OR (central.registro LIKE '%deletou título%' AND central.registro LIKE '%pelo motivo:%')
                        OR (central.registro LIKE '%cancelou título%')
                      ) 
                      AND (admin.login LIKE '%$search%' OR central.login LIKE '%$search%' OR central.registro LIKE '%$search%')
                    ";

    if (!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
        $startDate = mysqli_real_escape_string($link, $_GET['startDate']);
        $endDate = mysqli_real_escape_string($link, $_GET['endDate']);
        $query .= " AND DATE(central.data) BETWEEN '$startDate' AND '$endDate'";
    }

    if (isset($_GET['searchType']) && in_array($_GET['searchType'], ['titulos', 'parcelas', 'carne', 'cancelou'])) {
        switch ($_GET['searchType']) {
            case 'titulos':
                $query .= " AND central.registro LIKE '%deletou título%'";
                break;
            case 'parcelas':
                $query .= " AND central.registro LIKE '%deletou parcela%'";
                break;
            case 'carne':
                $query .= " AND (central.registro LIKE '%deletou o carne%' OR central.registro LIKE '%deletou carne%')";
                break;
            case 'cancelou':
                $query .= " AND central.registro LIKE '%cancelou título%'";
                break;
        }
    }

    $query .= "))";
}

$total_boletos_excluidos = 0;
$total_cancelados = 0;
$valor_total = 0;
$total_vencido = 0;
$total_aberto = 0;
$total_deletado = 0;
$total_outros = 0;

$query .= " ORDER BY order_date DESC";
$result = mysqli_query($link, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Extrair o usuário que deletou do texto do registro
        $usuario_deletou = '';
        
        // Padrão: "nome do usuário deletou/cancelou..."
        if (preg_match('/^(.+?)\s+(deletou|cancelou)\s+/i', $row['registro'], $user_matches)) {
            $usuario_deletou = trim($user_matches[1]);
        }
        
        // Se não encontrou no padrão acima, usa o admin_login ou login do row
        if (empty($usuario_deletou)) {
            $usuario_deletou = $row['admin_login'] ? $row['admin_login'] : $row['login'];
        }
        
        // Extrai o ID do registro usando expressão regular
        preg_match('/(deletou|cancelou) (parcela|o carne|carne|titulo|título) (\w+)/i', $row['registro'], $matches);
        $id = isset($matches[3]) ? $matches[3] : '';

        $valor = '';
        $status = '';
        
        // Verificar se é carnê ou boleto individual
        $is_carne = (stripos($row['registro'], 'deletou o carne') !== false || 
                     stripos($row['registro'], 'deletou carne') !== false);
        
        if (!empty($id) && !$is_carne) {
            // Para boletos individuais e parcelas
            $valorQuery = "SELECT valor, status FROM sis_lanc WHERE id = $id";
            $valorResult = mysqli_query($link, $valorQuery);
            if ($valorResult && mysqli_num_rows($valorResult) > 0) {
                $valorRow = mysqli_fetch_assoc($valorResult);
                $valor = $valorRow['valor'];
                $status = $valorRow['status'];
                $valor_total += floatval(str_replace(',', '.', $valor));
                
                // Contabilizar status
                switch(strtolower($status)) {
                    case 'vencido':
                        $total_vencido++;
                        break;
                    case 'aberto':
                        $total_aberto++;
                        break;
                    case 'deletado':
                        $total_deletado++;
                        break;
                    default:
                        if (!empty($status)) {
                            $total_outros++;
                        }
                        break;
                }
            }
        }

        $valor_carne_total = 0;
        $status_carne_array = array();
        if (!empty($id) && $is_carne) {
            // Buscar todas as parcelas do carnê
            $valorCarneQuery = "SELECT valor, status FROM sis_lanc WHERE codigo_carne = '$id'";
            $valorCarneResult = mysqli_query($link, $valorCarneQuery);
            
            if ($valorCarneResult && mysqli_num_rows($valorCarneResult) > 0) {
                while ($valorCarneRow = mysqli_fetch_assoc($valorCarneResult)) {
                    $valor_parcela = floatval(str_replace(',', '.', $valorCarneRow['valor']));
                    $valor_carne_total += $valor_parcela;
                    $valor_total += $valor_parcela;
                    
                    // Guardar status de cada parcela
                    $status_parcela = strtolower($valorCarneRow['status']);
                    if (!empty($status_parcela)) {
                        if (!isset($status_carne_array[$status_parcela])) {
                            $status_carne_array[$status_parcela] = 0;
                        }
                        $status_carne_array[$status_parcela]++;
                        
                        // Contabilizar status das parcelas do carnê
                        switch($status_parcela) {
                            case 'vencido':
                                $total_vencido++;
                                break;
                            case 'aberto':
                                $total_aberto++;
                                break;
                            case 'deletado':
                                $total_deletado++;
                                break;
                            default:
                                $total_outros++;
                                break;
                        }
                    }
                }
            }
        }

        if (stripos($row['registro'], 'cancelou') !== false) {
            $total_cancelados++;
        }
        
        $total_boletos_excluidos++;

        $order_date_timestamp = strtotime(str_replace('/', '-', $row['order_date']));
        $order_date_formatted = $order_date_timestamp ? date('d/m/Y H:i:s', $order_date_timestamp) : '';

        // Definir cor do badge de status
        $status_badge_class = 'badge-success';
        $status_display = '';
        
        // Se for carnê, mostrar resumo dos status
        if (!empty($status_carne_array)) {
            $status_parts = array();
            foreach ($status_carne_array as $st => $count) {
                $status_parts[] = ucfirst($st) . ': ' . $count;
            }
            $status_display = implode(' | ', $status_parts);
            $status_badge_class = 'badge-info'; // Cor diferente para carnê
        } elseif (!empty($status)) {
            $status_display = ucfirst($status);
            switch(strtolower($status)) {
                case 'vencido':
                    $status_badge_class = 'badge-danger';
                    break;
                case 'aberto':
                    $status_badge_class = 'badge-success';
                    break;
                case 'deletado':
                    $status_badge_class = 'badge-warning';
                    break;
                default:
                    $status_badge_class = 'badge-secondary';
                    break;
            }
        } else {
            $status_display = 'N/A';
            $status_badge_class = 'badge-secondary';
        }

        // Filtrar por status se solicitado
        $show_row = true;
        if ($searchStatus != 'all') {
            $row_status = strtolower($status);
            
            // Para carnês, verificar se tem o status no array
            if (!empty($status_carne_array)) {
                $show_row = isset($status_carne_array[$searchStatus]);
            } else {
                $show_row = ($row_status == $searchStatus);
            }
            
            // Filtro especial para cancelados
            if ($searchStatus == 'cancelado') {
                $show_row = (stripos($row['registro'], 'cancelou') !== false);
            }
        }

        if (!$show_row) {
            continue;
        }
        //Login
        echo "<tr>";
        echo "<td style='max-width: 300px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'>";
        echo "<a href=\"javascript:void(0);\" onclick=\"searchByTipoCob('".$row['login']."')\" title='" . htmlspecialchars($row['login']) . "'>";
        echo "<img src='img/icon_cliente.png' class='icon-inline'>";
        echo "<span class='login-clickable' style='font-weight: bold;'>" . $row['login'] . "</span>";
        echo "</a></td>";

        
		//Data Exclusão	
        echo "<td><span class='badge badge-success'>" . $order_date_formatted . "</span></td>";
        
        //Usuário
        echo "<td style='max-width: 120px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'>";
        echo "<a href=\"javascript:void(0);\" onclick=\"searchByTipoCob('".$usuario_deletou."')\" title='" . htmlspecialchars($usuario_deletou) . "'>";
        echo "<span class='login-clickable' style='font-weight: bold;'>" . $usuario_deletou . "</span>";
        echo "</a></td>";

        
        //Registro
        echo "<td style='max-width: 200px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; color: #f35812; cursor: help; font-weight: bold;' title='" . htmlspecialchars($row['registro']) . "'>";
        echo $row['registro'];
        echo "</td>";

        
        //Valor
        echo "<td style='min-width: 100px;'>";
        if ($valor !== '') echo "<img src='img/icon_boleto.png' class='icon-inline'><span style='font-weight: bold;'>R$ $valor</span><br>";
        if ($valor_carne_total > 0) echo "<img src='img/icon_boleto.png' class='icon-inline'><span style='font-weight: bold;'>R$ " . number_format($valor_carne_total, 2, ',', '.') . "</span> <small style='color: #666;'>(Carnê)</small>";
        echo "</td>";

        
		//Status
        echo "<td><span class='badge " . $status_badge_class . "' style='white-space: nowrap;' title='" . htmlspecialchars($status_display) . "'>" . $status_display . "</span></td>";
        
		//ID
        echo "<td style='max-width: 200px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'>";
        echo "<a href=\"javascript:void(0);\" onclick=\"searchById('".$id."')\" title='" . htmlspecialchars($id) . "'>";
        echo "<img src='img/digital.png' class='icon-inline'>";
        echo "<span class='login-clickable' style='color: #007ff7;'>" . $id . "</span>";
        echo "</a></td>";
        echo "</tr>";
    }
}
?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // Atualizar estatísticas dinamicamente
        document.getElementById('total-excluidos').textContent = <?php echo $total_boletos_excluidos; ?>;
        document.getElementById('total-cancelados').textContent = <?php echo $total_cancelados; ?>;
        document.getElementById('valor-total').textContent = 'R$ <?php echo number_format($valor_total, 2, ',', '.'); ?>';
        document.getElementById('total-vencido').textContent = <?php echo $total_vencido; ?>;
        document.getElementById('total-aberto').textContent = <?php echo $total_aberto; ?>;
        //document.getElementById('total-deletado').textContent = <?php echo $total_deletado; ?>;
        //document.getElementById('total-outros').textContent = <?php echo $total_outros; ?>;
        
        const startDate = new Date('<?php echo $startDate; ?>');
        const endDate = new Date('<?php echo $endDate; ?>');
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        document.getElementById('periodo').textContent = diffDays <= 1 ? 'Hoje' : diffDays + ' dias';

        // Destacar chip ativo
        const currentStatus = '<?php echo $searchStatus; ?>';
        if (currentStatus !== 'all') {
            const activeChip = document.getElementById('chip-' + currentStatus);
            if (activeChip) {
                activeChip.classList.add('active');
            }
        }

        function filterByStatus(status) {
            // Não filtrar para os cards que não tem status
            if (status === 'valor' || status === 'periodo' || status === 'all') {
                if (status === 'all') {
                    document.getElementById('searchStatus').value = 'all';
                    document.getElementById('searchForm').submit();
                }
                return;
            }
            
            document.getElementById('searchStatus').value = status;
            document.getElementById('searchForm').submit();
        }

        function clearSearch() {
            document.getElementById('search').value = '';
            document.getElementById('startDate').value = '<?php echo date('Y-m-d'); ?>';
            document.getElementById('endDate').value = '<?php echo date('Y-m-d', strtotime('+1 day')); ?>';
            document.getElementById('searchType').value = 'all';
            document.getElementById('searchStatus').value = 'all';
            document.getElementById('searchForm').submit();
        }

        function searchByTipoCob(login) {
            document.getElementById('search').value = login;
            document.getElementById('searchForm').submit();
        }

        function searchById(id) {
            document.getElementById('search').value = id;
            document.getElementById('searchForm').submit();
        }

        $(document).ready(function() {
            $('.login-clickable').click(function() {
                var login = $(this).text();
                $('#search').val(login);
                $('#searchForm').submit();
            });
        });
    </script>

    <?php } else { echo "<div class='container-wrapper'><div class='search-card'><h2 style='color: #e74c3c;'>Acesso não permitido!</h2></div></div>"; } ?>

    <?php include('../../baixo.php'); ?>
    <script src="../../menu.js.php"></script>
    <?php include('../../rodape.php'); ?>
</body>
</html>