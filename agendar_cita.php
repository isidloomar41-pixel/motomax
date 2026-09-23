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
    
    $servicio_id = $_GET['id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id = ?");
    $stmt->execute([$servicio_id]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servicio) {
        die("Servicio no encontrado");
    }
    
    $mensaje = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $moto = $_POST['moto'];
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        
        $sql = "INSERT INTO citas_taller (servicio_id, cliente_nombre, cliente_telefono, cliente_moto, fecha_cita, hora_cita) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$servicio_id, $nombre, $telefono, $moto, $fecha, $hora]);
        
        $mensaje = "? ¡Cita agendada exitosamente! Te esperamos el $fecha a las $hora.";
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agendar Cita - <?= htmlspecialchars($servicio['nombre']) ?> - MotoMax</title>
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
        .servicio {
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
        <h1>??? Agendar Cita - Taller</h1>
        <?php if ($mensaje): ?>
            <div class="mensaje"><?= $mensaje ?></div>
            <a href="index.php" class="back">? Volver</a>
        <?php else: ?>
        <div class="servicio">
            <h2><?= htmlspecialchars($servicio['nombre']) ?></h2>
            <p><?= htmlspecialchars($servicio['descripcion']) ?></p>
            <div class="precio">$<?= number_format($servicio['precio'], 2) ?></div>
            <p>Duración: <?= htmlspecialchars($servicio['duracion_aprox']) ?></p>
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
                <label>Marca y modelo de tu moto *</label>
                <input type="text" name="moto" placeholder="Ej: Honda CBR 600RR 2020" required>
            </div>
            <div class="form-group">
                <label>Fecha deseada *</label>
                <input type="date" name="fecha" required>
            </div>
            <div class="form-group">
                <label>Hora deseada *</label>
                <input type="time" name="hora" required>
            </div>
            <button type="submit" class="btn">Agendar Cita</button>
        </form>
        <a href="index.php" class="back">? Volver</a>
        <?php endif; ?>
    </div>
</body>
</html>
