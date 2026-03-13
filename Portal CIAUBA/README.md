## 📋 Requisitos

- Servidor web (Apache)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Composer

## 🚀 Instalación

1. Clona este repositorio en tu servidor local.
2. Crea una base de datos llamada `cia_uba` e importa el archivo `database.sql` (incluido en el proyecto).
3. Configura los datos de conexión en `config/database.php`.
4. Ejecuta `composer install` en la raíz del proyecto.
5. Accede a `http://localhost/login/public/`

## 📁 Estructura del proyecto

( Aquí incluye el árbol de directorios explicado anteriormente )

## 🔧 Funcionalidades

- Registro de usuarios con contraseña hasheada.
- Inicio de sesión con verificación de credenciales.
- Protección de rutas (solo usuarios autenticados pueden ver el dashboard).
- Cierre de sesión.
- Diseño responsive básico.

## 🛠️ Tecnologías utilizadas

- PHP 8.x
- MySQL
- PDO
- Composer (autoload)
- HTML5 / CSS3