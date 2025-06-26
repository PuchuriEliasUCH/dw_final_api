# Api del Sistema de Donaciones SolidaridApp

## Tecnologías y herramientas

- PHP 8.2.12
- MySQL 8.0.42
- Postman (para pruebas)
- Arquitectura MVC
- Manejo de sesiones
- Control de acceso por roles (`admin`, `organizacion`, `donante`)

## Estructura del proyecto

/phpApi
│
├── config/ # Conexion a base de datos
│ └── database.php
│
├── controllers/ # funcionalidad
│ └── AuthController.php
│ └── CategoriaController.php
│ └── DonacionController.php
│ └── EstadisticaController.php
│ └── NecesidadController.php
│ └── OrganizacionController.php
│ └── UsuarioController.php
│
├── models/ 
│ └── Donacion.php
│ └── Donante.php
│ └── Necesidad.php
│ └── Organizacion.php
│ └── Usuario.php
│
├── utils/ 
│ ├── Auth.php
│ ├── Response.php # Gestión de sesiones
│ └── Validator.php # Salida estándar en JSON
│
├── index.php # Router principal
└── .htaccess # Reescritura de URLs si se usa Apache