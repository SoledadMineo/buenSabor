# Roles y acceso inicial

## Roles disponibles

El proyecto define `admin`, `empleado` y `cliente` en `App\Enums\Rol`.
El rol de un empleado se obtiene de `empleado.rol`; el rol cliente se reconoce
por la relación entre `usuario` y `cliente`. Una cuenta sin perfil asociado
puede iniciar sesión, pero no recibe un rol ni permisos administrativos.

## Acceso aplicado

| Área | Visitante | Cliente | Empleado | Admin |
| --- | --- | --- | --- | --- |
| Catálogo y consultas de artículos | Permitido | Permitido | Permitido | Permitido |
| Ver grilla administrativa | Denegado | Denegado | Denegado | Permitido |
| Crear insumos | Denegado | Denegado | Denegado | Permitido |

La restricción se aplica en React para orientar la navegación y en Laravel para
proteger los datos aunque se llame a la API directamente. `empleado` queda sin
permisos operativos hasta definir sus tareas concretas (por ejemplo, pedidos o
stock); no se le conceden permisos por defecto.

## Pendiente

- Definir permisos operativos de empleados y alcance de clientes autenticados.
- Proteger futuros endpoints administrativos con `auth:sanctum` y `role`.
- Probar en navegador los rechazos 401/403 y el acceso administrativo con una
  cuenta de cada rol.
