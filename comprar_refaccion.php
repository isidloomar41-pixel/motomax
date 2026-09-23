<?php
header('Content-Type: text/html; charset=utf-8');

// Conexión a la base de datos usando URL completa
$db_url = 'postgresql://usuario_sitio:t8E11W1Pqb5hFwoLkzdZOXHzirB7cwbt@dpg-dapl2ns9v7es739087fg-a:5432/db_mi_sitio_web';

// Parsear la URL
$parts = parse_url($db_url);
$host = $parts['host'];
$port = $parts['port'] ?? '5432';
$dbname = ltrim($parts['path'], '/');
$user = $parts['user'];
$password = $parts['pass'];

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $refaccion_id = $_GET['id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT * FROM refacciones WHERE id = ?");
    $stmt->execute([$refaccion_id]);
    $refaccion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$refaccion) {
        die("Refacción no encontrada");
    }
    
    $mensaje = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $cantidad = $_POST['cantidad'];
        $total = $refaccion['precio'] * $cantidad;
        
        $sql = "INSERT INTO ventas_refacciones (refaccion_id, cantidad, cliente_nombre, cliente_telefono, total) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$refaccion_id, $cantidad, $nombre, $telefono, $total]);
        
        $mensaje = "? ¡Compra de refacción registrada! Total: $" . number_format($total, 2);
        
        $pdo->prepare("UPDATE refacciones SET stock = stock - ? WHERE id = ?")->execute([$cantidad, $refaccion_id]);
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprar <?= htmlspecialchars($refaccion['nombre']) ?> - MotoMax</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0d0d0d;
            color: #fff;
        }
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
        .producto {
            background: #2d2d2d;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .precio { font-size: 28px; font-weight: bold; color: #e63946; margin: 15px 0; }
        form { display: flex; flex-direction: column; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        label { margin-bottom: 8px; }
        input {
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
    <header><div class="logo">Moto<span>Max</span></div></header>
    <div class="container">
        <h1>?? Comprar Refacción</h1>
        <?php if ($mensaje): ?>
            <div class="mensaje"><?= $mensaje ?></div>
            <a href="index.php" class="back">? Volver</a>
        <?php else: ?>
        <div class="producto">
            <h2><?= htmlspecialchars($refaccion['nombre']) ?></h2>
            <p><?= htmlspecialchars($refaccion['descripcion']) ?></p>
            <div class="precio">$<?= number_format($refaccion['precio'], 2) ?></div>
            <p>Stock disponible: <?= $refaccion['stock'] ?></p>
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
                <label>Cantidad *</label>
                <input type="number" name="cantidad" min="1" max="<?= $refaccion['stock'] ?>" value="1" required>
            </div>
            <button type="submit" class="btn">Comprar</button>
        </form>
        <a href="index.php" class="back">? Volver</a>
        <?php endif; ?>
    </div>
</body>
</html>
