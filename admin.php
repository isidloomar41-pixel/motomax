<?php
header('Content-Type: text/html; charset=utf-8');

// Conexión a la base de datos
$host = getenv('PGHOST') ?: 'dpg-dapl2ns9v7es739087fg-a';
$port = getenv('PGPORT') ?: '5432';
$dbname = getenv('PGDATABASE') ?: 'db_mi_sitio_web';
$user = getenv('PGUSER') ?: 'usuario_sitio';
$password = getenv('PGPASSWORD') ?: 't8E11W1Pqb5hFwoLkzdZOXHzirB7cwbt';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener ventas de motos
    $stmt = $pdo->query("
        SELECT vm.*, m.modelo, m.marca_id, ma.nombre AS marca 
        FROM ventas_motos vm 
        JOIN motos m ON vm.moto_id = m.id 
        JOIN marcas ma ON m.marca_id = ma.id 
        ORDER BY vm.fecha DESC
    ");
    $ventas_motos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener ventas de refacciones
    $stmt2 = $pdo->query("
        SELECT vr.*, r.nombre AS refaccion 
        FROM ventas_refacciones vr 
        JOIN refacciones r ON vr.refaccion_id = r.id 
        ORDER BY vr.fecha DESC
    ");
    $ventas_refacciones = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener citas del taller
    $stmt3 = $pdo->query("
        SELECT ct.*, s.nombre AS servicio 
        FROM citas_taller ct 
        JOIN servicios s ON ct.servicio_id = s.id 
        ORDER BY ct.fecha_cita DESC
    ");
    $citas = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular totales
    $total_motos = count($ventas_motos);
    $total_refacciones = count($ventas_refacciones);
    $total_citas = count($citas);
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - MotoMax</title>
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
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #e63946;
        }
        .logo span { color: #fff; }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px;
        }
        h1 {
            color: #e63946;
            margin-bottom: 30px;
            text-align: center;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: #1a1a1a;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e63946;
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #e63946;
        }
        .stat-label {
            color: #888;
            margin-top: 10px;
            font-size: 16px;
        }
        .section {
            background: #1a1a1a;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .section h2 {
            color: #e63946;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        th {
            background: #2d2d2d;
            color: #e63946;
            font-weight: 600;
        }
        tr:hover {
            background: #2d2d2d;
        }
        .estado {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .pendiente {
            background: #f39c12;
            color: #000;
        }
        .confirmada {
            background: #3498db;
            color: #fff;
        }
        .entregada {
            background: #27ae60;
            color: #fff;
        }
        .back {
            display: inline-block;
            margin-bottom: 20px;
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
        <div class="logo">Moto<span>Max</span> - Admin</div>
    </header>

    <div class="container">
        <a href="index.php" class="back">? Volver al sitio</a>
        <h1>Panel de Administración</h1>
        
        <!-- ESTADÍSTICAS -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $total_motos ?></div>
                <div class="stat-label">Motos Vendidas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_refacciones ?></div>
                <div class="stat-label">Refacciones Vendidas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_citas ?></div>
                <div class="stat-label">Citas Agendadas</div>
            </div>
        </div>

        <!-- VENTAS DE MOTOS -->
        <div class="section">
            <h2>Ventas de Motos</h2>
            <?php if (empty($ventas_motos)): ?>
                <p style="color: #888;">No hay ventas registradas aún.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Moto</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Pago</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas_motos as $venta): ?>
                    <tr>
                        <td><?= $venta['id'] ?></td>
                        <td><?= htmlspecialchars($venta['marca']) ?> <?= htmlspecialchars($venta['modelo']) ?></td>
                        <td><?= htmlspecialchars($venta['cliente_nombre']) ?></td>
                        <td><?= htmlspecialchars($venta['cliente_telefono']) ?></td>
                        <td><?= htmlspecialchars($venta['cliente_email']) ?></td>
                        <td><?= htmlspecialchars($venta['forma_pago']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></td>
                        <td><span class="estado <?= strtolower($venta['estado']) ?>"><?= htmlspecialchars($venta['estado']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- VENTAS DE REFACCIONES -->
        <div class="section">
            <h2>Ventas de Refacciones</h2>
            <?php if (empty($ventas_refacciones)): ?>
                <p style="color: #888;">No hay ventas registradas aún.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Refacción</th>
                        <th>Cantidad</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Total</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas_refacciones as $venta): ?>
                    <tr>
                        <td><?= $venta['id'] ?></td>
                        <td><?= htmlspecialchars($venta['refaccion']) ?></td>
                        <td><?= $venta['cantidad'] ?></td>
                        <td><?= htmlspecialchars($venta['cliente_nombre']) ?></td>
                        <td><?= htmlspecialchars($venta['cliente_telefono']) ?></td>
                        <td>$<?= number_format($venta['total'], 2) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- CITAS DEL TALLER -->
        <div class="section">
            <h2>Citas del Taller</h2>
            <?php if (empty($citas)): ?>
                <p style="color: #888;">No hay citas agendadas aún.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Servicio</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Moto</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                    <tr>
                        <td><?= $cita['id'] ?></td>
                        <td><?= htmlspecialchars($cita['servicio']) ?></td>
                        <td><?= htmlspecialchars($cita['cliente_nombre']) ?></td>
                        <td><?= htmlspecialchars($cita['cliente_telefono']) ?></td>
                        <td><?= htmlspecialchars($cita['cliente_moto']) ?></td>
                        <td><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></td>
                        <td><?= date('H:i', strtotime($cita['hora_cita'])) ?></td>
                        <td><span class="estado <?= strtolower($cita['estado']) ?>"><?= htmlspecialchars($cita['estado']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
