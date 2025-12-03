<?php
session_start();
// --- CONFIGURAÇÃO DO BANCO DE DADOS ---
$host = 'localhost';
$user = 'root';      
$pass = '';          
$dbname = 'gamevault';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div class='alert alert-danger'>Erro na conexão com o banco: " . $e->getMessage() . "<br>Verifique se rodou o script SQL da Parte 01.</div>");
}

// --- LOGICA DE ROTEAMENTO E AÇÕES (CONTROLLER) ---
$action = $_GET['action'] ?? 'list';
$message = '';

// 1. AÇÃO: REMOVER (DELETE)
if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM Jogo WHERE id_jogo = ?");
    if ($stmt->execute([$_GET['id']])) {
        header("Location: index.php?msg=deleted");
        exit;
    }
}

// 2. AÇÃO: SALVAR (CREATE OU UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_jogo'] ?? null;
    $titulo = $_POST['titulo'];
    $preco = $_POST['preco'];
    $ano = $_POST['ano_lancamento'];
    $franquia = $_POST['id_franquia_fk'];

    if ($id) {
        // Update
        $sql = "UPDATE Jogo SET titulo=?, preco=?, ano_lancamento=?, id_franquia_fk=? WHERE id_jogo=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $preco, $ano, $franquia, $id]);
        header("Location: index.php?msg=updated");
    } else {
        // Create
        $sql = "INSERT INTO Jogo (titulo, preco, ano_lancamento, id_franquia_fk) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $preco, $ano, $franquia]);
        header("Location: index.php?msg=created");
    }
    exit;
}

// Mensagens de feedback
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') $message = '<div class="alert alert-warning">Jogo removido com sucesso!</div>';
    if ($_GET['msg'] == 'created') $message = '<div class="alert alert-success">Jogo cadastrado com sucesso!</div>';
    if ($_GET['msg'] == 'updated') $message = '<div class="alert alert-info">Jogo atualizado com sucesso!</div>';
}



$franquias = $pdo->query("SELECT f.id_franquia, f.titulo, d.nome as dev_nome FROM Franquia f JOIN Desenvolvedora d ON f.id_dev_fk = d.id_desenvolvedora ORDER BY f.titulo")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameVault Manager</title>
    <!-- Bootstrap 5 CSS (Visual Moderno) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome (Ícones) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #e0e0e0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { background-color: #1e1e1e; border: 1px solid #333; }
        .table { color: #e0e0e0; }
        .table-hover tbody tr:hover { color: #fff; background-color: #2c2c2c; }
        .form-control, .form-select { background-color: #2c2c2c; border: 1px solid #444; color: #fff; }
        .form-control:focus, .form-select:focus { background-color: #2c2c2c; color: #fff; border-color: #0d6efd; box-shadow: none; }
        .btn-primary { background-color: #6200ea; border-color: #6200ea; }
        .btn-primary:hover { background-color: #3700b3; border-color: #3700b3; }
        .navbar { border-bottom: 2px solid #6200ea; }
    </style>
</head>
<body>


<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-gamepad me-2"></i>GameVault Manager</a>
    </div>
    <div class="d-flex text-white align-items-center">
            <a href="login.php" class="btn btn-sm btn-light text-primary fw-bold">Sair</a>
        </div>
</nav>

<div class="container">
    <?= $message ?>

    <div class="row">
       
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <?php 
                        // Lógica para preencher o formulário se for Edição
                        $editData = null;
                        if ($action === 'edit' && isset($_GET['id'])) {
                            $stmt = $pdo->prepare("SELECT * FROM Jogo WHERE id_jogo = ?");
                            $stmt->execute([$_GET['id']]);
                            $editData = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    ?>
                    <h5 class="mb-0"><i class="fas <?= $editData ? 'fa-edit' : 'fa-plus-circle' ?>"></i> <?= $editData ? 'Editar Jogo' : 'Novo Jogo' ?></h5>
                </div>
                <div class="card-body">
                    <form action="index.php" method="POST">
                        <input type="hidden" name="id_jogo" value="<?= $editData['id_jogo'] ?? '' ?>">
                        
                        <div class="mb-3 text-white">
                            <label class="form-label">Título do Jogo</label>
                            <input type="text" name="titulo" class="form-control" required value="<?= $editData['titulo'] ?? '' ?>" placeholder="Ex: Elden Ring">
                        </div>

                        <div class="mb-3 text-white">
                            <label class="form-label">Franquia (Desenvolvedora)</label>
                            <select name="id_franquia_fk" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($franquias as $f): ?>
                                    <option value="<?= $f['id_franquia'] ?>" 
                                        <?= (isset($editData) && $editData['id_franquia_fk'] == $f['id_franquia']) ? 'selected' : '' ?>>
                                        <?= $f['titulo'] ?> (<?= $f['dev_nome'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-white">A franquia vincula o jogo à desenvolvedora.</div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3 text-white">
                                <label class="form-label">Preço (R$)</label>
                                <input type="number" step="0.01" name="preco" class="form-control" required value="<?= $editData['preco'] ?? '' ?>">
                            </div>
                            <div class="col-6 mb-3 text-white">
                                <label class="form-label">Ano</label>
                                <input type="number" name="ano_lancamento" class="form-control" required value="<?= $editData['ano_lancamento'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Jogo</button>
                            <?php if($editData): ?>
                                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- LADO DIREITO: LISTAGEM (READ) -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-dark text-white border-bottom border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Catálogo de Jogos</h5>
                   
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dark table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Franquia & Dev</th>
                                    <th>Ano</th>
                                    <th>Preço</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // JOIN entre Jogo, Franquia e Desenvolvedora para mostrar dados completos
                                $sqlList = "
                                    SELECT j.*, f.titulo as franquia, d.nome as dev 
                                    FROM Jogo j
                                    JOIN Franquia f ON j.id_franquia_fk = f.id_franquia
                                    JOIN Desenvolvedora d ON f.id_dev_fk = d.id_desenvolvedora
                                    ORDER BY j.id_jogo DESC
                                ";
                                $listaJogos = $pdo->query($sqlList);

                                if ($listaJogos->rowCount() > 0):
                                    while($jogo = $listaJogos->fetch(PDO::FETCH_ASSOC)):
                                ?>
                                <tr>
                                    <td>#<?= $jogo['id_jogo'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($jogo['titulo']) ?></td>
                                    <td>
                                        <small class="d-block text-info"><?= htmlspecialchars($jogo['franquia']) ?></small>
                                        <small class="text-muted"><?= htmlspecialchars($jogo['dev']) ?></small>
                                    </td>
                                    <td><?= $jogo['ano_lancamento'] ?></td>
                                    <td class="text-success">R$ <?= number_format($jogo['preco'], 2, ',', '.') ?></td>
                                    <td class="text-end">
                                        <a href="index.php?action=edit&id=<?= $jogo['id_jogo'] ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <a href="index.php?action=delete&id=<?= $jogo['id_jogo'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja apagar este jogo?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Nenhum jogo cadastrado. Utilize o formulário ao lado.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-center text-muted mt-5 mb-3">
    <small>Desenvolvido para a Disciplina de Banco de Dados I, Thiago Moraers Ludvig e Andrey &copy; 2025</small>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>