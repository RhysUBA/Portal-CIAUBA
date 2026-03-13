<?php
require_once __DIR__ . '/../vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIAUBA - Información</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <img src="img/logo-uba-horizontal1.png" alt="uba_logo">
        <div class="logo">
            <h1>Club de Ingeniería Aplicada de la Universidad Bicentenaria de Aragua</h1>
            <p>Aprende • Construye • Mejora</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="information.php">Información</a></li>
                <?php if (User::estaLogueado()): ?>
                    <li><a href="members.php">Miembros</a></li>
                    <li><a href="work_together.php">Foro</a></li>
                    <li><a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                    <?php if (User::esAdmin()): ?>
                        <li><a href="admin.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar sesión (<?php echo $_SESSION['usuario_nombre']; ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Iniciar sesión</a></li>
                    <li><a href="register.php">Registro</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

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
                <h3>Información de contacto</h3>
                <p>Para más información sobre el club de inginiería aplicada, por favor contacta:</p>
                <address>
                    <p><strong>Correo:</strong> rhysuba@gmail.com</p>
                    <p><strong>Oficina:</strong> Laboratorio de Realidad Virtual</p>
                    <p><strong>Teléfono:</strong> 04248313052</p>
                </address>
            </article>
        </section>
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
        <p>Universidad Bicentenaria de Aragua</p>
        <p>Contact: rhysuba@gmail.com | Campus Edificio de Ingeniería, Salón de Realidad Virtual</p>
    </footer>
</body>
</html>