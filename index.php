<?php
header('Content-Type: text/html; charset=utf-8');

// Conexión a la base de datos
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
    
    $stmt = $pdo->query("SELECT m.*, ma.nombre AS marca, c.nombre AS categoria FROM motos m JOIN marcas ma ON m.marca_id = ma.id JOIN categorias c ON m.categoria_id = c.id ORDER BY m.id");
    $motos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt2 = $pdo->query("SELECT * FROM refacciones ORDER BY id");
    $refacciones = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt3 = $pdo->query("SELECT * FROM servicios ORDER BY id");
    $servicios = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Imágenes de motos por ID (URLs de Unsplash)
$imagenes_motos = [
    1 => 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=600',
    2 => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=600',
    3 => 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=600',
    4 => 'https://images.unsplash.com/photo-1591637333184-19aa84b3e01f?w=600',
    5 => 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=600',
];

$imagenes_refacciones = [
    1 => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=600',
    2 => 'https://images.unsplash.com/photo-1635784065408-7e0f0a4a4b5e?w=600',
    3 => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600',
    4 => 'https://images.unsplash.com/photo-1530046339160-ce3e530c7d2f?w=600',
    5 => 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=600',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoMax - Concesionaria</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0d0d0d; color: #fff; }
        
        header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #e63946;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo { font-size: 28px; font-weight: bold; color: #e63946; text-transform: uppercase; letter-spacing: 2px; }
        .logo span { color: #fff; }
        nav a { color: #fff; text-decoration: none; margin-left: 30px; font-weight: 500; transition: color 0.3s; }
        nav a:hover { color: #e63946; }
        
        .hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1600') center/cover;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero h1 { font-size: 60px; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 20px; }
        .hero h1 span { color: #e63946; }
        .hero p { font-size: 20px; color: #ccc; margin-bottom: 30px; }
        
        .btn {
            background: #e63946; color: #fff; padding: 15px 40px; border: none;
            border-radius: 5px; font-size: 16px; cursor: pointer; text-decoration: none;
            display: inline-block; transition: background 0.3s;
        }
        .btn:hover { background: #c1121f; }
        
        .seccion { padding: 60px 50px; }
        .seccion h2 { font-size: 36px; margin-bottom: 40px; text-align: center; text-transform: uppercase; letter-spacing: 2px; }
        .seccion h2::after { content: ''; display: block; width: 80px; height: 4px; background: #e63946; margin: 15px auto 0; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto; }
        
        .card {
            background: #1a1a1a; border-radius: 10px; overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s; border: 1px solid #333;
        }
        .card:hover { transform: translateY(-10px); box-shadow: 0 10px 30px rgba(230, 57, 70, 0.3); }
        
        .card-img {
            height: 200px;
            background-size: cover;
            background-position: center;
        }
        
        .card-body { padding: 20px; }
        .card-body h3 { color: #e63946; margin-bottom: 10px; }
        .card-body .marca { color: #888; font-size: 14px; margin-bottom: 10px; }
        .card-body .specs { color: #ccc; font-size: 14px; margin-bottom: 15px; line-height: 1.6; }
        .card-body .precio { font-size: 24px; font-weight: bold; color: #fff; margin-bottom: 15px; }
        .card-body .btn { width: 100%; text-align: center; padding: 12px; }
        
        .servicio-card {
            background: #1a1a1a; padding: 30px; border-radius: 10px; text-align: center;
            border: 1px solid #333; transition: transform 0.3s;
        }
        .servicio-card:hover { transform: scale(1.05); border-color: #e63946; }
        .servicio-card h3 { color: #e63946; margin-bottom: 15px; }
        .servicio-card .precio { font-size: 28px; font-weight: bold; margin: 15px 0; }
        
        .contacto { background: #141414; padding: 60px 50px; text-align: center; }
        .contacto h2 { color: #e63946; margin-bottom: 30px; }
        .contacto-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; max-width: 1000px; margin: 0 auto; }
        .contacto-item { background: #1a1a1a; padding: 30px; border-radius: 10px; border: 1px solid #333; }
        .contacto-item h3 { color: #e63946; margin-bottom: 15px; }
        .contacto-item p { color: #ccc; line-height: 1.8; }
        
        footer { background: #1a1a1a; padding: 30px; text-align: center; border-top: 3px solid #e63946; color: #888; }
    </style>
</head>
<body>

    <header>
        <div class="logo">Moto<span>Max</span></div>
        <nav>
            <a href="#motos">Motos</a>
            <a href="#refacciones">Refacciones</a>
            <a href="#taller">Taller</a>
            <a href="#contacto">Contacto</a>
        </nav>
    </header>

    <section class="hero">
        <div>
            <h1>Tu próxima <span>moto</span> te espera</h1>
            <p>Las mejores marcas, los mejores precios, el mejor servicio</p>
            <a href="#motos" class="btn">Ver Catálogo</a>
        </div>
    </section>

    <section class="seccion" id="motos">
        <h2>Nuestras Motos</h2>
        <div class="grid">
            <?php foreach ($motos as $moto): 
                $img = $imagenes_motos[$moto['id']] ?? 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=600';
            ?>
            <div class="card">
                <div class="card-img" style="background-image: url('<?= $img ?>')"></div>
                <div class="card-body">
                    <div class="marca"><?= htmlspecialchars($moto['marca']) ?> - <?= htmlspecialchars($moto['categoria']) ?></div>
                    <h3><?= htmlspecialchars($moto['modelo']) ?> <?= $moto['anio'] ?></h3>
                    <p class="specs">
                        Cilindrada: <?= $moto['cilindrada'] ?>cc<br>
                        Color: <?= htmlspecialchars($moto['color']) ?><br>
                        Stock: <?= $moto['stock'] ?> unidades
                    </p>
                    <p class="precio">$<?= number_format($moto['precio'], 2) ?></p>
                    <a href="comprar_moto.php?id=<?= $moto['id'] ?>" class="btn">Comprar</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="seccion" id="refacciones" style="background: #141414;">
        <h2>Refacciones y Accesorios</h2>
        <div class="grid">
            <?php foreach ($refacciones as $ref): 
                $img = $imagenes_refacciones[$ref['id']] ?? 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=600';
            ?>
            <div class="card">
                <div class="card-img" style="background-image: url('<?= $img ?>')"></div>
                <div class="card-body">
                    <div class="marca"><?= htmlspecialchars($ref['categoria']) ?></div>
                    <h3><?= htmlspecialchars($ref['nombre']) ?></h3>
                    <p class="specs"><?= htmlspecialchars($ref['descripcion']) ?></p>
                    <p class="precio">$<?= number_format($ref['precio'], 2) ?></p>
                    <a href="comprar_refaccion.php?id=<?= $ref['id'] ?>" class="btn">Comprar</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="seccion" id="taller">
        <h2>Servicios de Taller</h2>
        <div class="grid">
            <?php foreach ($servicios as $serv): ?>
            <div class="servicio-card">
                <h3><?= htmlspecialchars($serv['nombre']) ?></h3>
                <p style="color: #ccc;"><?= htmlspecialchars($serv['descripcion']) ?></p>
                <div class="precio">$<?= number_format($serv['precio'], 2) ?></div>
                <p style="color: #888; margin-bottom: 15px;">Duración: <?= htmlspecialchars($serv['duracion_aprox']) ?></p>
                <a href="agendar_cita.php?id=<?= $serv['id'] ?>" class="btn">Agendar Cita</a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="contacto" id="contacto">
        <h2>Contáctanos</h2>
        <div class="contacto-info">
            <div class="contacto-item">
                <h3>Dirección</h3>
                <p>Av. Principal #123<br>Col. Centro, Toluca<br>Estado de México</p>
            </div>
            <div class="contacto-item">
                <h3>Teléfono</h3>
                <p>(722) 123-4567<br>(722) 765-4321</p>
            </div>
            <div class="contacto-item">
                <h3>Horario</h3>
                <p>Lunes a Viernes: 9:00 - 19:00<br>Sábados: 10:00 - 15:00<br>Domingos: Cerrado</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 MotoMax - Concesionaria de Motos. Todos los derechos reservados.</p>
    </footer>

</body>
</html>
