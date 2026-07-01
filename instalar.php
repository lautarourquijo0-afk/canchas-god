<?php
/* ============================================================
   INSTALADOR — entrá UNA vez a  /instalar.php
   Crea las tablas, carga turnos de ejemplo para hoy y crea los
   usuarios del panel. Se puede correr de nuevo sin romper nada.
   Cuando termines, BORRÁ este archivo por seguridad.
   ============================================================ */

require __DIR__ . '/conexion.php';

header('Content-Type: text/html; charset=utf-8');
echo '<meta charset="utf-8"><body style="font-family:sans-serif;max-width:620px;margin:40px auto;line-height:1.7;color:#222">';
echo '<h2>⚙️ Instalación del turnero — Turin Sport</h2>';

try {
    /* ---- TABLAS ---- */
    $pdo->exec("CREATE TABLE IF NOT EXISTS sedes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(60) NOT NULL,
        direccion VARCHAR(160) NOT NULL,
        orden INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS turnos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sede_id INT NOT NULL,
        fecha DATE NOT NULL,
        hora_inicio TIME NOT NULL,
        hora_fin TIME NOT NULL,
        cancha VARCHAR(40) NOT NULL DEFAULT 'Cancha 1',
        cliente VARCHAR(80) NULL,
        estado ENUM('reservado','libre') NOT NULL DEFAULT 'reservado',
        FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(40) NOT NULL UNIQUE,
        clave_hash VARCHAR(255) NOT NULL,
        rol ENUM('admin','recepcion') NOT NULL DEFAULT 'recepcion'
    ) ENGINE=InnoDB");

    echo '✅ Tablas creadas.<br>';

    /* ---- SEDES (solo si están vacías) ---- */
    if ($pdo->query("SELECT COUNT(*) FROM sedes")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO sedes (nombre, direccion, orden) VALUES
            ('Sede 1', 'San Leonardo Murialdo 909', 1),
            ('Sede 2', 'La Cautiva 7654', 2)");
        echo '✅ Sedes cargadas.<br>';
    } else {
        echo 'ℹ️ Sedes ya existían (no se tocaron).<br>';
    }

    /* ---- TURNOS de ejemplo para HOY (solo si no hay para hoy) ---- */
    $hayHoy = $pdo->query("SELECT COUNT(*) FROM turnos WHERE fecha = CURDATE()")->fetchColumn();
    if ($hayHoy == 0) {
        $ids = $pdo->query("SELECT id FROM sedes ORDER BY orden, id")->fetchAll(PDO::FETCH_COLUMN);
        $A = $ids[0] ?? 1; $B = $ids[1] ?? $A;
        $ins = $pdo->prepare("INSERT INTO turnos
            (sede_id,fecha,hora_inicio,hora_fin,cancha,cliente,estado)
            VALUES (?,CURDATE(),?,?,?,?,?)");
        $demo = [
            [$A,'08:00','09:00','Cancha 1','Matías G.','reservado'],
            [$A,'09:00','10:00','Cancha 2','Club Atletismo','reservado'],
            [$A,'10:00','11:00','Cancha 1','Pablo R.','reservado'],
            [$A,'11:00','12:00','Cancha 1',null,'libre'],
            [$A,'16:00','17:00','Cancha 3','Sebastián L.','reservado'],
            [$A,'17:00','18:00','Cancha 1','Ramírez, J.','reservado'],
            [$A,'20:00','21:00','Cancha 3',null,'libre'],
            [$A,'21:00','22:00','Cancha 1','Jorge S.','reservado'],
            [$B,'09:00','10:00','Cancha 1','Escuelita de Fútbol','reservado'],
            [$B,'10:00','11:00','Cancha 2','Gonzalo P.','reservado'],
            [$B,'13:00','14:00','Cancha 2',null,'libre'],
            [$B,'16:00','17:00','Cancha 2','Luciana R.','reservado'],
            [$B,'18:00','19:00','Cancha 1','Torneo Interno','reservado'],
            [$B,'21:00','22:00','Cancha 2',null,'libre'],
        ];
        foreach ($demo as $d) $ins->execute($d);
        echo '✅ Turnos de ejemplo para hoy cargados.<br>';
    } else {
        echo 'ℹ️ Ya había turnos para hoy (no se duplicaron).<br>';
    }

    /* ---- USUARIOS del panel ---- */
    $up = $pdo->prepare("INSERT INTO usuarios (usuario, clave_hash, rol) VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE clave_hash = VALUES(clave_hash), rol = VALUES(rol)");
    $up->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
    $up->execute(['recepcion', password_hash('recepcion123', PASSWORD_DEFAULT), 'recepcion']);
    echo '✅ Usuarios creados: <b>admin / admin123</b> y <b>recepcion / recepcion123</b>.<br>';

    echo '<hr><p style="color:#b02820"><b>Importante:</b> cambiá esas contraseñas y '
       . '<b>borrá este archivo (instalar.php)</b> del repo.</p>';
    echo '<p><a href="/admin/">→ Ir al panel</a> &nbsp;|&nbsp; <a href="/pantalla/index.html">→ Ver la pantalla</a></p>';

} catch (Throwable $e) {
    echo '<p style="color:#c00"><b>Error:</b> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Revisá que el servicio MySQL esté conectado (variables) y que el deploy haya terminado.</p>';
}
