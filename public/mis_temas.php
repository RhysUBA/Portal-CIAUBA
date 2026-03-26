<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$temaModel = new Tema();

// Temas creados por el usuario
$temas_creados = $temaModel->obtenerPorUsuario($usuario_id);
// Temas donde ha participado (respondido)
$temas_participados = $temaModel->obtenerParticipados($usuario_id);

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
        .tema-item {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-md);
            margin-bottom: var(--space-md);
            box-shadow: var(--shadow-sm);
        }
    ';
$page_title = 'Mis Temas - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <section class="user-section">
            <h2>Mis Temas en el Foro</h2>
            
            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('creados')">Creados por mí (<?php echo count($temas_creados); ?>)</button>
                <button class="tab-btn" onclick="showTab('participados')">Donde participé (<?php echo count($temas_participados); ?>)</button>
            </div>

            <div id="tab-creados" class="tab-content active">
                <?php if (empty($temas_creados)): ?>
                    <p>Aún no has creado ningún tema.</p>
                <?php else: ?>
                    <?php foreach ($temas_creados as $tema): ?>
                        <div class="tema-item">
                            <h3><a href="tema.php?id=<?php echo $tema['id']; ?>"><?php echo htmlspecialchars($tema['titulo']); ?></a></h3>
                            <p><?php echo htmlspecialchars(substr($tema['contenido'], 0, 150)) . '...'; ?></p>
                            <div class="tema-meta">
                                <span><i class="fas fa-comments"></i> <?php echo $tema['num_respuestas']; ?> respuestas</span>
                                <span><i class="fas fa-eye"></i> <?php echo $tema['visitas']; ?> visitas</span>
                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($tema['categoria_nombre']); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($tema['creado_en'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="tab-participados" class="tab-content">
                <?php if (empty($temas_participados)): ?>
                    <p>Aún no has participado en ningún tema.</p>
                <?php else: ?>
                    <?php foreach ($temas_participados as $tema): ?>
                        <div class="tema-item">
                            <h3><a href="tema.php?id=<?php echo $tema['id']; ?>"><?php echo htmlspecialchars($tema['titulo']); ?></a></h3>
                            <p>Por: <?php echo htmlspecialchars($tema['nombre']); ?></p>
                            <div class="tema-meta">
                                <span><i class="fas fa-comments"></i> <?php echo $tema['num_respuestas']; ?> respuestas</span>
                                <span><i class="fas fa-eye"></i> <?php echo $tema['visitas']; ?> visitas</span>
                                <span><i class="fas fa-calendar"></i> Última actividad: <?php echo date('d/m/Y', strtotime($tema['actualizado_en'] ?? $tema['creado_en'])); ?></span>
                            </div>
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