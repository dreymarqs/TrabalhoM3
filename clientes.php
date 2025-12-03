<?php
session_start();

$host = 'localhost';
$dbname = 'gamevault';
$user = 'root';
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}


if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'cliente') {
    header("Location: login.php");
    exit;
}


// Proteção: Se não for cliente, redireciona
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'cliente') {
    header("Location: login.php");
    exit;
}

// ==========================================================
// LÓGICA DE COMPRA (INTERAÇÃO COM PEDIDO E PEDIDO_JOGO)
// ==========================================================
if (isset($_GET['acao']) && $_GET['acao'] == 'comprar' && isset($_GET['id_jogo'])) {
    try {
        $id_jogo = $_GET['id_jogo'];
        $id_cliente = $_SESSION['usuario_id'];

        // 1. Busca o preço atual do jogo (para garantir integridade histórica)
        $stmtPreco = $pdo->prepare("SELECT preco FROM Jogo WHERE id_jogo = ?");
        $stmtPreco->execute([$id_jogo]);
        $jogo = $stmtPreco->fetch(PDO::FETCH_ASSOC);

        if ($jogo) {
            $preco = $jogo['preco'];

            // 2. Cria o PEDIDO (Cabeçalho)
            // Aqui criamos um pedido novo para essa compra única
            $stmtPedido = $pdo->prepare("INSERT INTO Pedido (id_cliente_fk, valor_total, data_emissao) VALUES (?, ?, NOW())");
            $stmtPedido->execute([$id_cliente, $preco]);
            
            // Pega o ID do pedido que acabamos de criar
            $id_pedido_gerado = $pdo->lastInsertId();

            // 3. Cria o PEDIDO_JOGO (Item do pedido)
            // Vincula o jogo ao pedido criado acima
            $stmtItem = $pdo->prepare("INSERT INTO Pedido_jogo (id_pedido_fk, id_jogo_fk, quantidade, valor_unitario_no_momento) VALUES (?, ?, 1, ?)");
            $stmtItem->execute([$id_pedido_gerado, $id_jogo, $preco]);

            // Redireciona com mensagem de sucesso
            header("Location: clientes.php?msg=sucesso");
            exit;
        }
    } catch (PDOException $e) {
        die("Erro ao processar compra: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Loja GameVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .game-card { transition: transform 0.2s; cursor: pointer; }
        .game-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .price-tag { font-size: 1.2rem; font-weight: bold; color: #28a745; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-gamepad me-2"></i>GameVault Store</a>
        <div class="d-flex text-white align-items-center">
            <span class="me-3">Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
            <a href="login.php" class="btn btn-sm btn-light text-primary fw-bold">Sair</a>
        </div>
    </div>
</nav>

<div class="container">
    
    <!-- Mensagem de Feedback -->
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <strong>Sucesso!</strong> Compra realizada. Verifique seu histórico.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h2 class="mb-4 text-secondary">Jogos Disponíveis</h2>
    
    <div class="row">
        <?php
        $sql = "SELECT j.*, f.titulo as franquia, d.nome as dev 
                FROM Jogo j 
                JOIN Franquia f ON j.id_franquia_fk = f.id_franquia 
                JOIN Desenvolvedora d ON f.id_dev_fk = d.id_desenvolvedora";
        
        // Verifica se a conexão existe (garantia)
        if (isset($pdo)) {
            $stmt = $pdo->query($sql);

            if ($stmt->rowCount() > 0):
                while($jogo = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
            <div class="col-md-3 mb-4">
                <div class="card game-card h-100 border-0 shadow-sm">
                    <div class="bg-dark text-white d-flex align-items-center justify-content-center" style="height: 150px;">
                        <i class="fas fa-image fa-3x text-secondary"></i>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($jogo['titulo']) ?></h5>
                        <p class="card-text text-muted small mb-1">
                            <?= htmlspecialchars($jogo['franquia']) ?> • <?= htmlspecialchars($jogo['dev']) ?>
                        </p>
                        <p class="card-text text-muted small">Ano: <?= $jogo['ano_lancamento'] ?></p>
                        
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="price-tag">R$ <?= number_format($jogo['preco'], 2, ',', '.') ?></span>
                            
                            <!-- BOTÃO DE COMPRAR AGORA FUNCIONAL -->
                            <!-- Envia o ID do jogo e a ação 'comprar' via URL -->
                            <a href="?acao=comprar&id_jogo=<?= $jogo['id_jogo'] ?>" 
                               class="btn btn-outline-primary btn-sm"
                               onclick="return confirm('Confirmar a compra de <?= htmlspecialchars($jogo['titulo']) ?> por R$ <?= number_format($jogo['preco'], 2, ',', '.') ?>?')">
                                <i class="fas fa-shopping-cart"></i> Comprar
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        <?php 
                endwhile; 
            else:
        ?>
            <div class="col-12 text-center text-muted">Nenhum jogo disponível no momento.</div>
        <?php 
            endif;
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

