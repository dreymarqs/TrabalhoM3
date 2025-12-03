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

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $tipo = $_POST['tipo']; 

    if ($tipo === 'admin') {
       
        $stmt = $pdo->prepare("SELECT * FROM Usuario WHERE email = ? AND senha = ?");
        $stmt->execute([$email, $senha]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['usuario_nome'] = $user['nickname'];
            $_SESSION['perfil'] = 'admin';
            header("Location: index.php"); 
            exit;
        }
    } else {
        // Verifica na tabela Cliente
        $stmt = $pdo->prepare("SELECT * FROM Cliente WHERE email = ? AND senha = ?");
        $stmt->execute([$email, $senha]);
        $cli = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cli) {
            $_SESSION['usuario_id'] = $cli['id_cliente'];
            $_SESSION['usuario_nome'] = $cli['nome_completo'];
            $_SESSION['perfil'] = 'cliente';
            header("Location: clientes.php"); 
            exit;
        }
    }
    
    $erro = "E-mail ou senha incorretos!";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - GameVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card-login { width: 100%; max-width: 400px; background: #1e1e1e; border: 1px solid #333; color: #fff; }
        .form-control { background: #2c2c2c; border-color: #444; color: #fff; }
        .form-control:focus { background: #2c2c2c; color: #fff; box-shadow: none; border-color: #6200ea; }
        .btn-purple { background-color: #6200ea; color: white; }
        .btn-purple:hover { background-color: #3700b3; color: white; }
    </style>
</head>
<body>
    <div class="card card-login p-4 shadow">
        <h3 class="text-center mb-4">GameVault <span class="text-info">Login</span></h3>
        
        <?php if($erro): ?>
            <div class="alert alert-danger p-2 text-center"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>E-mail</label>
                <input type="email" name="email" class="form-control" required placeholder="seu@email.com">
            </div>
            <div class="mb-3">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control" required placeholder="******">
            </div>

            <div class="mb-3 text-center">
                <label class="d-block mb-2">Quem é você?</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="tipo" id="t_cli" value="cliente" checked>
                    <label class="btn btn-outline-info" for="t_cli">Sou Cliente</label>

                    <input type="radio" class="btn-check" name="tipo" id="t_adm" value="admin">
                    <label class="btn btn-outline-warning" for="t_adm">Sou Admin</label>
                </div>
            </div>

            <button type="submit" class="btn btn-purple w-100 py-2 fw-bold">ENTRAR</button>
        </form>
        
        <div class="mt-3 text-center text-white small">
            <p>Admin: admin@gamevault.com / admin123</p>
            <p>Cliente: cliente@gmail.com / cliente123</p>
        </div>
    </div>
    
</body>
</html>

