# README.md

## Estructura del Proyecto

Este proyecto es una aplicación web basada en Laravel que incluye autenticación de usuarios y diferentes roles con permisos específicos. A continuación se detalla la estructura y las rutas disponibles.

### Roles y Permisos
Arquitectura Spatie integrada de roles y permisos

- **Usuarios autenticados**: Acceso básico al dashboard y gestión de perfil.
- **Administradores (`admin` )**: Gestión completa de usuarios.
- **Gestores (`gestor`, `editor`)**: Acceso limitado a funciones específicas. 
- **Gestores (`teacher`, `student`)**: Acceso limitado a funciones específicas. 
- **Gestores (`user`, `invitado`)**: Acceso limitado a funciones específicas. 

### Estilos de botones

Puedes usar los componentes de botones 
resources\views\components\campus-button.blade.php

Ejemplo: variantes: header, primary, secondary, danger, 
#### En formularios

<x-campus-button type="submit" variant="primary">
    Guardar
</x-campus-button>

#### En acciones neutras
<x-campus-button
    href="{{ route('campus.categories.create') }}"
    variant="header">
    <i class="bi bi-plus-lg me-2"></i>
    {{ __('Categoria') }}
</x-campus-button>

#### Eliminar
<x-campus-button
    type="submit"
    variant="danger"
    onclick="return confirm('¿Segur?')"
>
    Eliminar
</x-campus-button>




### Rutas Disponibles

#### Rutas Públicas
- `/`: Página de bienvenida.

#### Rutas de Autenticación
- Rutas generadas por `auth.php` (login, registro, recuperación de contraseña, etc.).

#### Rutas Protegidas (requieren autenticación)
- **Dashboard**:
  - `/dashboard`: Panel principal para usuarios autenticados.

- **Perfil de Usuario**:
  - `/profile`: Edición, actualización y eliminación del perfil.

- **Administrador** (requiere rol `admin`):
  - `/admin/users`: CRUD completo de usuarios (índice, creación, almacenamiento, edición, actualización, eliminación).

### Controladores

- `DashboardController`: Maneja la vista principal del dashboard.
- `ProfileController`: Gestiona las operaciones relacionadas con el perfil del usuario.
- `xxxController`: Gestiona las operaciones relacionadas xxx.
- `Admin\xxxController`: Controlador de recursos para la gestión administrativa por parte del administrador.
- `Api\xxxController`: Controlador de recursos para la api.


### Middlewares

- `auth`: Asegura que el usuario esté autenticado.
- `verified`: Verifica que el correo electrónico del usuario esté confirmado.
- `role`: Restringe el acceso basado en roles (`admin`).


### Instalación y Configuración

#### Clonar el repositorio, `git clone git@github.com:martinartacho/mhartacho.git `
#### Ejecutar `composer install` para instalar las dependencias. 
`npm install && npm run dev`  la primera vez.
`npm run dev` Las siguientes veces 
#### Configurar el archivo `.env` con los datos de la base de datos.
#### Ejecuta `php artisan key:generate`
##### Ejecutar las migraciones con `php artisan migrate`.
#### Opcional: Ejecutar los seeders para rellenar la BBDD con datos de prueba.
`php artisan db:seed`
o Ejecutar un seeder específico: `php artisan db:seed --class=NotificationsTableSeeder`


### Notas

- Asegúrese de que los roles `admin` y `gestor` estén correctamente configurados en el sistema de permisos.
- Las rutas comentadas en `web.php` pueden ser reactivadas según necesidades específicas.

Para más detalles, consulte la documentación de Laravel o los comentarios en el código fuente.

### SOBRE LA API
Esta API está desarrollada en Laravel y utiliza JWT para autenticación. Está desplegada en:

🔗 https://nomdominio/api

---

## 🔐 Autenticación (JWT)

### Login
**POST** `/api/login`

**Parámetros:**
```json
{
  "email": "usuario@example.com",
  "password": "Pass.Seg.123"
}
```

**Respuesta:**
```json
{
  "access_token": "jwt_token",
  "token_type": "bearer",
  "expires_in": 3600
}
```

---

## 👤 Perfil del usuario

### Obtener usuario autenticado
**GET** `/api/me`  
**Header:** `Authorization: Bearer {token}`

### Actualizar perfil
**PUT** `/api/profile`  
**Body:** `{ "name": "Nuevo Nombre", "email": "nuevo@email.com" }`

---

## 🔒 Seguridad

### Cambiar contraseña
**PUT** `/api/change-password`  
```json
{
  "current_password": "anterior",
  "new_password": "nueva"
}
```

### Eliminar cuenta
**DELETE** `/api/delete-account`

---

## 🔔 Notificaciones

### Guardar token FCM
**POST** `/api/save-fcm-token`  
**Header:** `Authorization: Bearer {token}`  
```json
{
  "fcm_token": "firebase_token"
}
```

---

## 🧪 Test de logging
**GET** `/api/test-log`  
Genera un warning en `laravel.log`.

---

## ℹ️ Notas
- Las rutas protegidas requieren token JWT en la cabecera `Authorization`.
- No uses `curl -k` salvo para pruebas con certificados no verificados.

---

## ✨ Colores Personalizados en Tailwind
- Se ha editado  archivo tailwind.config.js para usar nombres semánticos como "success", usando Tailwind.

Ejemplo de uso:
```<span class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-green-600 rounded-full transform translate-x-1/2 -translate-y-1/2">
{{ $event->answers_count }}
</span>
```
- Después de modificar tailwind.config.js, recompila los estilos ejecutando

```
npm run dev
# o
npm run build
```

---

## Autor
Artacho DevTeam ✨# formacio-org
