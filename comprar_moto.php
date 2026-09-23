<?php
header('Content-Type: text/html; charset=utf-8');

// Conexión a la base de datos
$host = 'dpg-dapl2ns9v7es739087fg-a.ondigitalocean.com';
$port = '5432';
$dbname = 'db_mi_sitio_web';
$user = 'usuario_sitio';
$password = 't8E11W1Pqb5hFwoLkzdZOXHzirB7cwbt';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener ID de la moto
    $moto_id = $_GET['id'] ?? 0;
    
    // Obtener datos de la moto
    $stmt = $pdo->prepare("
        SELECT m.*, ma.nombre AS marca, c.nombre AS categoria 
        FROM motos m 
        JOIN marcas ma ON m.marca_id = ma.id 
        JOIN categorias c ON m.categoria_id = c.id 
        WHERE m.id = ?
    ");
    $stmt->execute([$moto_id]);
    $moto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$moto) {
        die("Moto no encontrada");
    }
    
    $mensaje = '';
    
    // Procesar formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];
        $forma_pago = $_POST['forma_pago'];
        
        $sql = "INSERT INTO ventas_motos (moto_id, cliente_nombre, cliente_telefono, cliente_email, forma_pago) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$moto_id, $nombre, $telefono, $email, $forma_pago]);
        
        $mensaje = "? ¡Compra registrada exitosamente! Nos pondremos en contacto contigo pronto.";
        
        // Actualizar stock
        $pdo->prepare("UPDATE motos SET stock = stock - 1 WHERE id = ?")->execute([$moto_id]);
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
    <title>Comprar <?= htmlspecialchars($moto['modelo']) ?> - MotoMax</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0d0d0d;
            color: #fff;
            min-height: 100vh;
        }
        header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 20px 50px;
            border-bottom: 3px solid #e63946;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #e63946;
        }
        .logo span { color: #fff; }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background: #1a1a1a;
            border-radius: 10px;
        }
        h1 {
            color: #e63946;
            margin-bottom: 20px;
        }
        .moto-info {
            background: #2d2d2d;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .moto-info h2 {
            margin-bottom: 10px;
        }
        .moto-info p {
            color: #ccc;
            margin: 5px 0;
        }
        .precio {
            font-size: 32px;
            font-weight: bold;
            color: #e63946;
            margin: 20px 0;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 8px;
            font-weight: 500;
        }
        input, select {
            padding: 12px;
            border: 1px solid #444;
            border-radius: 5px;
            background: #2d2d2d;
            color: #fff;
            font-size: 16px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #e63946;
        }
        .btn {
            background: #e63946;
            color: #fff;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn:hover {
            background: #c1121f;
        }
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
        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Moto<span>Max</span></div>
    </header>

    <div class="container">
        <h1>??? Comprar Moto</h1>
        
        <?php if ($mensaje): ?>
            <div class="mensaje"><?= $mensaje ?></div>
            <a href="index.php" class="back">? Volver al inicio</a>
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
                <label for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono *</label>
                <input type="tel" id="telefono" name="telefono" required>
            </div>
            
            <div class="form-group">
                <label for="email">Correo electrónico *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="forma_pago">Forma de pago *</label>
                <select id="forma_pago" name="forma_pago" required>
                    <option value="">Seleccione...</option>
                    <option value="Contado">Contado</option>
                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                    <option value="Financiamiento">Financiamiento</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Confirmar Compra</button>
        </form>
        
        <a href="index.php" class="back">? Volver al catálogo</a>
        <?php endif; ?>
    </div>
</body>
</html>
