<?php
require_once __DIR__ . '/../vendor/autoload.php';

$extra_css = '
    .partners-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--space-lg);
        margin-top: var(--space-lg);
    }
    .partner-card {
        background: white;
        border-radius: var(--border-radius-lg);
        padding: var(--space-xl);
        text-align: center;
        transition: all var(--transition-normal);
        border: 1px solid #e9ecef;
    }
    .partner-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }
    .partner-logo {
        width: 120px;
        height: 120px;
        margin: 0 auto var(--space-md);
        border-radius: 50%;
        overflow: hidden;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .partner-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
';

$page_title = 'Información - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <section class="club-info">
            <h2>Sobre nosotros</h2>
            <p>El club de ingeniería aplicada es una organización llevada por estudiantes y apoyada por la univervisad dedicada en proveer un entorno controlado y colaborativo donde estudiantes de ingeniería puedan desarrollar sus habilidades prácticas mediante proyectos y sesiones de estudios.</p>
            
            <article class="info-section">
                <h3>Misión</h3>
                <p>Nuestro objetivo es llenar el vacio entre el conocimiento teórico y la aplicación práctica proveyendo recursos, mentorías y un espacio para estudiantes en el que trabajar en proyectos a escala real y profesional, ayudandoles a mejorar sus habilidades y preparar sus portafolios para su carrera profesional y futura búsqueda de empleo.</p>
            </article>
            
            <article class="info-section">
                <h3>¿Qué ofrecemos?</h3>
                <ul>
                    <li><strong>Espacio de desarrollo de proyectos:</strong> Aceso a nuestro laboratorio de realidad virtual con herramientas básicas, electrónica e impresoras 3D</li>
                    <li><strong>Mentorías:</strong> Guía de miembros senior y de los consejeros de la facultad</li>
                    <li><strong>Talleres:</strong> Talleres regulares en tecnologías y metodologías relevantes</li>
                    <li><strong>Entorno colaborativo:</strong> Trabaja con otros estudiantes con diferentes disciplinas</li>
                    <li><strong>Construcción de portafolio:</strong> Documenta y presenta tus proyectos para futuros empleadores</li>
                    <li><strong>Competencia:</strong> Participa en hackathons y competiciones de desarrollo.</li>
                </ul>
            </article>
            
            <article class="info-section">
                <h3>Estructura</h3>
                <p>El club está organizado en equipos, cada uno enfocado en retar diferentes áreas de la ingeniería. Los equipos de desarrollo consisten típicamente de 3-4 miembros con habilidades complementarias y se reunen semanalmente para registrar su progreso y resaltar problemas a solucionar.</p>
                
                <h4>Equipo administrativo</h4>
                <ul>
                    <li><strong>Presidente:</strong> Supervisa las operaciones del club y las relaciones externas, así como ser el contacto principal entre estudiantes y la universidad</li>
                    <li><strong>Vice Presidente:</strong> Maneja asuntos internos y la interacción entre los miembros</li>
                    <li><strong>Coordinador de proyectos:</strong> Facilita la organización de equipos y la alocación de recursos</li>
                    <li><strong>Tesorero:</strong> Maneja el presupuesto del club y peticiones de fondos</li>
                    <li><strong>Líder técnico:</strong> Provee asistencia y organiza talleres</li>
                </ul>
            </article>
            
            <article class="info-section">
                <h3>Horario</h3>
                <?php
                $configModel = new Configuracion();
                $horario = $configModel->obtener('horario');
                if ($horario) {
                    echo $horario;
                } else {
                    // mostrar horario por defecto
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Día</th>
                            <th>Hora</th>
                            <th>Actividad</th>
                            <th>Ubicación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Monday</td>
                            <td>6:00 PM - 7:30 PM</td>
                            <td>General Meeting & Project Updates</td>
                            <td>Building 10, Room 205</td>
                        </tr>
                        <tr>
                            <td>Wednesday</td>
                            <td>5:00 PM - 8:00 PM</td>
                            <td>Open Lab & Collaborative Work</td>
                            <td>Engineering Lab 3</td>
                        </tr>
                        <tr>
                            <td>Friday</td>
                            <td>4:00 PM - 6:00 PM</td>
                            <td>Technical Workshops</td>
                            <td>Building 10, Room 210</td>
                        </tr>
                        <tr>
                            <td>Saturday</td>
                            <td>10:00 AM - 2:00 PM</td>
                            <td>Project Work Sessions</td>
                            <td>Engineering Lab 3</td>
                        </tr>
                    </tbody>
                </table>
                <?php } ?>
            </article>
            
            <article class="info-section">
                <h3>Recursos disponibles</h3>
                <ul>
                    <li>Impresoras 3D</li>
                    <li>Componentes básicos de electrónica y herramientas</li>
                    <li>Osciloscopios y multimetros</li>
                    <li>Kits Arduino/Raspberry Pi</li>
                    <li>Licencias de software CAD</li>
                    <li>Espacio de almacenaje de proyectos</li>
                    <li>Libros de referencia y manuales técnicos</li>
                </ul>
            </article>
            
            <article class="info-section">
                <h3>Asesores</h3>
                <p>Nuestro club se sostiene con el apoyo de dedicados miembros de la facultad que proveen guía y garantizan la alineación con los objetivos académicos:</p>
                <ul>
                    <li><strong>Jorge Enrique Aguilera Herrera</strong> - Departamento de Ingeniería en Sistemas</li>
                </ul>
            </article>
            
            <article class="info-section">
                <h3><i class="fas fa-handshake"></i> Socios Comerciales</h3>
                <p>Gracias a la colaboración con estas empresas, nuestros miembros tienen acceso a oportunidades profesionales, prácticas y formación especializada.</p>
                
                <div class="partners-grid">
                    <!-- ClassVR -->
                    <div class="partner-card">
                        <div class="partner-logo">
                            <img src="img/classvr.jpg" alt="ClassVR">
                            <i class="fas fa-vr-cardboard" style="font-size: 4rem; color: var(--color-light-blue);"></i>
                        </div>
                        <h3>ClassVR</h3>
                        <p>Tecnología de realidad virtual aplicada a la educación. Colaboramos en proyectos de innovación educativa y ofrecemos a nuestros miembros la posibilidad de formarse en entornos inmersivos.</p>
                        <a href="https://www.classvr.com" target="_blank" class="partner-link">Conocer más <i class="fas fa-external-link-alt"></i></a>
                    </div>

                    <!-- Algorithmics -->
                    <div class="partner-card">
                        <div class="partner-logo">
                            <img src="img/Algorithmics.jpg" alt="Algorithmics">
                            <i class="fas fa-laptop-code" style="font-size: 4rem; color: var(--color-light-blue);"></i>
                        </div>
                        <h3>Algorithmics</h3>
                        <p>Escuela internacional de programación para niños y jóvenes. A través de este convenio, nuestros miembros pueden participar como mentores y acceder a materiales didácticos de vanguardia.</p>
                        <a href="https://es.alg.academy/" target="_blank" class="partner-link">Conocer más <i class="fas fa-external-link-alt"></i></a>
                    </div>

                    <!-- BusinessKids -->
                    <div class="partner-card">
                        <div class="partner-logo">
                            <img src="img/businesskids.jpg" alt="BusinessKids">
                            <i class="fas fa-child" style="font-size: 4rem; color: var(--color-light-blue);"></i>
                        </div>
                        <h3>BusinessKids</h3>
                        <p>Programa de emprendimiento para niños. Nuestros miembros colaboran en el diseño de actividades lúdico-formativas y tienen oportunidades de prácticas en el área de tecnología educativa.</p>
                        <a href="https://businesskids.com.ve/" target="_blank" class="partner-link">Conocer más <i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
            </article>

            <!-- Resto del contenido (contacto, etc.) -->
            <article class="info-section">
                <h3>Información de contacto</h3>
                <address>
                    <p><strong>Correo:</strong> rhysuba@gmail.com</p>
                    <p><strong>Oficina:</strong> Laboratorio de Realidad Virtual</p>
                    <p><strong>Teléfono:</strong> 04248313052</p>
                </address>
            </article>
        </section>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>