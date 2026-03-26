<?php
require_once __DIR__ . '/../vendor/autoload.php';

$eventoModel = new Evento();

$proximos = $eventoModel->obtenerProximos(20);
$pasados = $eventoModel->obtenerPasados(20);

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
        .event-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-md);
            transition: transform var(--transition-fast), box-shadow var(--transition-fast);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--space-md);
        }
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .event-info {
            flex: 1;
        }
        .event-info h3 {
            margin-bottom: var(--space-xs);
        }
        .event-info h3 a {
            color: var(--color-dark-blue);
            text-decoration: none;
        }
        .event-meta {
            display: flex;
            gap: var(--space-md);
            font-size: 0.9rem;
            color: #666;
            margin-top: var(--space-xs);
        }
        .event-meta i {
            margin-right: 3px;
        }
        .event-badge {
            background: var(--color-info);
            color: white;
            padding: var(--space-xs) var(--space-sm);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .event-badge.pasado {
            background: #95a5a6;
        }
        .event-badge.proximo {
            background: var(--color-success);
        }
        .empty-state {
            text-align: center;
            padding: var(--space-xxl);
            background: white;
            border-radius: var(--radius-lg);
            color: #999;
        }
        @media (max-width: 768px) {
            .event-card {
                flex-direction: column;
                text-align: center;
            }
            .event-meta {
                flex-direction: column;
                align-items: center;
                gap: var(--space-xs);
            }
        }
    ';
$page_title = 'Eventos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="eventos-header">
            <h2><i class="fas fa-calendar-alt"></i> Eventos</h2>
            <p>Participa en nuestras actividades, talleres y reuniones.</p>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="mostrarTab('proximos')">Próximos (<?php echo count($proximos); ?>)</button>
            <button class="tab-btn" onclick="mostrarTab('pasados')">Pasados (<?php echo count($pasados); ?>)</button>
        </div>

        <div id="tab-proximos" class="tab-content active">
            <?php if (empty($proximos)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times" style="font-size: 4rem; color: #ddd; margin-bottom: var(--space-md);"></i>
                    <h3>No hay eventos próximos</h3>
                    <p>¡Pronto anunciaremos nuevas actividades!</p>
                </div>
            <?php else: ?>
                <?php foreach ($proximos as $evento): ?>
                    <div class="event-card">
                        <div class="event-info">
                            <h3><a href="evento.php?id=<?php echo $evento['id']; ?>"><?php echo htmlspecialchars($evento['titulo']); ?></a></h3>
                            <div class="event-meta">
                                <span><i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evento['lugar']); ?></span>
                                <span><i class="fas fa-tag"></i> <?php echo $evento['tipo']; ?></span>
                            </div>
                        </div>
                        <div class="event-badge proximo">Próximo</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="tab-pasados" class="tab-content">
            <?php if (empty($pasados)): ?>
                <div class="empty-state">
                    <i class="fas fa-history" style="font-size: 4rem; color: #ddd; margin-bottom: var(--space-md);"></i>
                    <h3>No hay eventos pasados</h3>
                </div>
            <?php else: ?>
                <?php foreach ($pasados as $evento): ?>
                    <div class="event-card">
                        <div class="event-info">
                            <h3><a href="evento.php?id=<?php echo $evento['id']; ?>"><?php echo htmlspecialchars($evento['titulo']); ?></a></h3>
                            <div class="event-meta">
                                <span><i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evento['lugar']); ?></span>
                                <span><i class="fas fa-tag"></i> <?php echo $evento['tipo']; ?></span>
                            </div>
                        </div>
                        <div class="event-badge pasado">Finalizado</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: var(--space-xl);">
            <a href="index.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>

    <script>
        function mostrarTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById('tab-' + tabName).classList.add('active');
        }
    </script>
</body>
</html>