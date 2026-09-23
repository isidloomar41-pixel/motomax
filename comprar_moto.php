<?php
header('Content-Type: text/html; charset=utf-8');

$db_url = 'postgresql://usuario_sitio:t8E11W1Pqb5hFwoLkzdZOXHzirB7cwbt@dpg-dapl2ns9v7es739087fg-a.oregon-postgres.render.com:5432/db_mi_sitio_web';
$parts = parse_url($db_url);
$host = $parts['host'];
$port = $parts['port'] ?? '5432';
$dbname = ltrim($parts['path'], '/');
$user = $parts['user'];
$password = $parts['pass'];

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $moto_id = $_GET['id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT m.*, ma.nombre AS marca, c.nombre AS categoria FROM motos m JOIN marcas ma ON m.marca_id = ma.id JOIN categorias c ON m.categoria_id = c.id WHERE m.id = ?");
    $stmt->execute([$moto_id]);
    $moto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$moto) {
        die("Moto no encontrada");
    }
    
    $mensaje = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];
        $pago = $_POST['forma_pago'];
        
        $sql = "INSERT INTO ventas_motos (moto_id, cliente_nombre, cliente_telefono, cliente_email, forma_pago, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$moto_id, $nombre, $telefono, $email, $pago]);
        
        $mensaje = "Compra registrada exitosamente. Nos pondremos en contacto contigo pronto.";
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Moto - <?= htmlspecialchars($moto['modelo']) ?> - MotoMax</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0d0d0d; color: #fff; }
        
        header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 20px 50px;
            border-bottom: 3px solid #e63946;
        }
        .logo { font-size: 28px; font-weight: bold; color: #e63946; }
        .logo span { color: #fff; }
        
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: #1a1a1a;
            border-radius: 10px;
        }
        
        h1 { color: #e63946; margin-bottom: 20px; }
        
        .moto-info {
            background: #2d2d2d;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .moto-info h2 { color: #fff; margin-bottom: 15px; }
        .moto-info p { color: #ccc; margin-bottom: 8px; }
        .precio { font-size: 32px; font-weight: bold; color: #e63946; margin: 20px 0; }
        
        form { display: flex; flex-direction: column; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        label { margin-bottom: 8px; color: #ccc; }
        input, select {
            padding: 12px;
            border: 1px solid #444;
            border-radius: 5px;
            background: #2d2d2d;
            color: #fff;
        }
        .btn {
            background: #e63946;
            color: #fff;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover { background: #c1121f; }
        
        .mensaje {
            background: #27ae60;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .back {
            display: inline-block;
            margin-top: 20px;
            color: #e63946;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Moto<span>Max</span></div>
    </header>
    
    <div class="container">
        <h1>Comprar Moto</h1>
        
        <?php if ($mensaje): ?>
            <div class="mensaje"><?= $mensaje ?></div>
            <a href="index.php" class="back">Volver al inicio</a>
        <?php else: ?>
        <div class="moto-info">
            <h2><?= htmlspecialchars($moto['marca']) ?> <?= htmlspecialchars($moto['modelo']) ?></h2>
            <p>Categoría: <?= htmlspecialchars($moto['categoria']) ?></p>
            <p>Año: <?= $moto['anio'] ?> | Cilindrada: <?= $moto['cilindrada'] ?>cc</p>
            <p>Color: <?= htmlspecialchars($moto['color']) ?></p>
            <div class="precio">$<?= number_format($moto['precio'], 2) ?></div>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Nombre completo *</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="form-group">
                <label>Teléfono *</label>
                <input type="tel" name="telefono" required>
            </div>
            <div class="form-group">
                <label>Correo electrónico *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Forma de pago *</label>
                <select name="forma_pago" required>
                    <option value="">Selecciona...</option>
                    <option value="Contado">Contado</option>
                    <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                    <option value="Tarjeta de débito">Tarjeta de débito</option>
                    <option value="Crédito">Crédito</option>
                </select>
            </div>
            <button type="submit" class="btn">Confirmar Compra</button>
        </form>
        <a href="index.php" class="back">Volver</a>
        <?php endif; ?>
    </div>
</body>
</html>
