<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$asistenteEventoModel = new AsistenteEvento();

// Obtener próximos eventos y pasados
$proximos = $asistenteEventoModel->obtenerEventosDeUsuario($usuario_id, true);
$pasados = $asistenteEventoModel->obtenerEventosDeUsuario($usuario_id, false);

$extra_css = '
        .tabs {
            display: flex;
            gap: var(--space-xs);
            margin-bottom: var(--space-lg);
            border-bottom: 2px solid rgba(12,43,78,0.1);
        }
        .tab-btn {
            background: none;
            border: none;
            padding: var(--space-sm) var(--space-lg);
            cursor: pointer;
            font-weight: 600;
            color: var(--color-medium-blue);
        }
        .tab-btn.active {
            color: var(--color-light-blue);
            border-bottom: 3px solid var(--color-light-blue);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .event-item {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-md);
            margin-bottom: var(--space-md);
            box-shadow: var(--shadow-sm);
        }
    ';
$page_title = 'Mis Eventos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <section class="user-section">
            <h2>Mis Eventos</h2>
            
            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('proximos')">Próximos (<?php echo count($proximos); ?>)</button>
                <button class="tab-btn" onclick="showTab('pasados')">Pasados (<?php echo count($pasados); ?>)</button>
            </div>

            <div id="tab-proximos" class="tab-content active">
                <?php if (empty($proximos)): ?>
                    <p>No tienes eventos próximos.</p>
                <?php else: ?>
                    <?php foreach ($proximos as $evento): ?>
                        <div class="event-item">
                            <h3><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                            <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?></p>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evento['lugar']); ?></p>
                            <p><i class="fas fa-users"></i> Asistentes: <?php echo $evento['total_asistentes']; ?></p>
                            <a href="evento.php?id=<?php echo $evento['id']; ?>" class="btn">Ver detalles</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="tab-pasados" class="tab-content">
                <?php if (empty($pasados)): ?>
                    <p>No tienes eventos pasados.</p>
                <?php else: ?>
                    <?php foreach ($pasados as $evento): ?>
                        <div class="event-item">
                            <h3><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                            <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?></p>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evento['lugar']); ?></p>
                            <p><i class="fas fa-check-circle" style="color: <?php echo $evento['asistio'] ? 'green' : 'red'; ?>"></i> 
                                <?php echo $evento['asistio'] ? 'Asististe' : 'No asististe'; ?>
                            </p>
                            <a href="evento.php?id=<?php echo $evento['id']; ?>" class="btn">Ver detalles</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById('tab-' + tabName).classList.add('active');
        }
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>