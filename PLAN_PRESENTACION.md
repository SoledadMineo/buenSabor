# Plan de finalización de El Buen Sabor

Objetivo actualizado al 20 de septiembre: cerrar durante noviembre de 2026
para presentar en diciembre. No hay una fecha de entrega obligatoria confirmada.

Disponibilidad estimada:

- Lunes a viernes: 1 hora por día.
- Sábados y domingos: al menos 3 horas (se asumen 3 horas cada día).
- Base de planificación: 11 horas semanales; las horas extra no se comprometen.
- Planificar 8 a 9 horas de tareas y reservar 2 a 3 para pruebas e imprevistos.

## Punto de retomada — 20 de septiembre de 2026

- La base configurada responde a `php artisan migrate:status`: todas las
  migraciones listadas están aplicadas, incluidas las dos del 13 de septiembre.
  Esto no verifica todavía los flujos ni la reconstrucción desde cero.
- Hay cambios locales pendientes de commit en los modelos de clientes,
  empleados, pedidos y facturas, la configuración de autenticación y migraciones.
- La API tiene consultas de artículos, categorías y unidades, y alta de insumos.
  Aún no hay rutas de gestión de clientes, empleados, pedidos ni login.
- `Usuario` todavía extiende `Model`: cambiar el proveedor de autenticación
  no completa por sí solo el acceso de usuarios.
- `DatabaseSeeder` no genera los datos de aceptación.
- Frontend: `npm.cmd run build` completado correctamente el 20 de septiembre.
- El trabajo sigue en el cierre de la base de la semana 1; la semana 2 no
  está completada. Las fechas originales de abajo quedan como referencia
  histórica y no representan compromisos diarios vigentes.

Próximos bloques, en orden:

1. Cerrar y comprobar las relaciones y migraciones actuales; documentar el
   modelo de datos y resolver los roles requeridos.
2. Implementar y probar autenticación y permisos.
3. Completar clientes y empleados de punta a punta, con datos de prueba.
4. Retomar insumos y manufacturados sobre esa base.

Trabajar con una meta verificable por sesión y reservar margen semanal para
correcciones. No trasladar automáticamente todas las tareas atrasadas al día
siguiente ni dar por terminado un módulo solo por tener sus modelos.

## Plan vigente por hitos

Noviembre es un objetivo, no una garantía de completar todo el alcance original.
La disponibilidad bajó aproximadamente a la mitad. Revisar el alcance y las
estimaciones cada domingo según los flujos efectivamente terminados.

| Período orientativo | Resultado a comprobar |
| --- | --- |
| 21–27 de septiembre | Base revisada, modelo y permisos documentados; definir autenticación |
| 28 de septiembre–11 de octubre | Acceso y permisos; clientes y empleados con flujo completo |
| 12–25 de octubre | Insumos, stock y manufacturados con recetas |
| 26 de octubre–8 de noviembre | Catálogo, carrito y pedido completo con impacto en stock |
| 9–22 de noviembre | Promociones, facturación y reportes requeridos, ajustando según avance |
| 23–30 de noviembre | Datos mínimos, pruebas por rol, documentación y ensayo |

Estos bloques son una propuesta inicial; no eliminan requisitos de aceptación.
Si un bloque se extiende, reestimar los siguientes en lugar de acumular tareas.

### Próxima semana: una meta concreta por sesión

- Lunes (1 h): revisar las relaciones cliente–pedido–factura y anotar pendientes.
- Martes (1 h): revisar usuario–cliente–empleado y documentar roles requeridos.
- Miércoles (1 h): revisar recetas e imágenes y completar el diagrama de datos.
- Jueves (1 h): revisar stock por sucursal y tipos monetarios.
- Viernes (1 h): definir cómo será el acceso de usuarios y sus criterios de prueba.
- Sábado (3 h): corregir los problemas priorizados y comprobar migraciones en
  una base de desarrollo separada, conservando la base actual.
- Domingo (3 h): probar relaciones, registrar avances y pendientes; usar el
  margen para lo que no haya entrado y ajustar la siguiente semana.

## Dinámica de cada jornada

Cada jornada comienza revisando la meta y termina con una prueba, un commit y una
nota breve de lo que queda pendiente. Un módulo solo se considera terminado si
tiene interfaz, API, validaciones, persistencia y prueba del flujo principal.

## Cronograma original — referencia histórica

### Inicio: domingo 6 de septiembre (4 a 6 h)

- [x] Auditar frontend, backend y datos existentes.
- [x] Registrar los requisitos y cantidades mínimas.
- [x] Corregir la compilación inicial del frontend.
- [x] Crear un respaldo exportable de la base MySQL actual (estructura y datos; confirmado por Soledad).
- [ ] Dibujar el modelo de datos corregido antes de modificar migraciones.
- [ ] Definir por escrito los roles y permisos de administrador, cajero,
  cocinero, delivery y cliente.

Resultado esperado: proyecto compilable y decisiones de arquitectura listas
para comenzar las correcciones de base de datos.

## Semana 1: 7 al 13 de septiembre — base estable

- Lunes (2 h): corregir relaciones y campos de clientes, empleados y usuarios.
- Martes (2 h): corregir pedidos, facturas y su relación con clientes.
- Miércoles (2 h): corregir recetas, imágenes y relaciones de artículos.
- Jueves (2 h): corregir stock por sucursal y tipos monetarios.
- Viernes (2 h): reconstruir una base de desarrollo y verificar migraciones.
- Sábado (4–6 h): implementar autenticación y protección de API.
- Domingo (4–6 h): roles, permisos y pruebas de acceso.

## Semana 2: 14 al 20 de septiembre — personas y usuarios

- Lunes: API de clientes.
- Martes: pantallas de clientes.
- Miércoles: domicilios y validaciones de clientes.
- Jueves: API de empleados.
- Viernes: pantallas de empleados y asignación de roles.
- Sábado: edición, baja lógica y filtros de ambos módulos.
- Domingo: pruebas y seeders de 15 clientes y 5 empleados con usuarios.

## Semana 3: 21 al 27 de septiembre — insumos

- Lunes: CRUD de unidades de medida.
- Martes: CRUD de categorías de insumos.
- Miércoles: completar API y validaciones de insumos.
- Jueves: completar alta y edición de insumos en React.
- Viernes: listado, filtros y baja lógica.
- Sábado: alta y ajuste de stock por sucursal.
- Domingo: alertas de stock y seeder de 40 insumos para elaborar y 15 de venta.

## Semana 4: 28 de septiembre al 4 de octubre — manufacturados

- Lunes: CRUD de categorías de manufacturados.
- Martes: API de productos manufacturados.
- Miércoles: receta y detalle de ingredientes.
- Jueves: cálculo automático del costo.
- Viernes: cálculo de disponibilidad según stock.
- Sábado: formulario completo con ingredientes e imágenes.
- Domingo: listado, edición, pruebas y seeder de 15 productos.

## Semana 5: 5 al 11 de octubre — catálogo y carrito

- Lunes: configuración centralizada de API y manejo de errores.
- Martes: catálogo con categorías y búsqueda.
- Miércoles: detalle y disponibilidad de productos.
- Jueves: estado global del carrito.
- Viernes: agregar, quitar y modificar cantidades.
- Sábado: totales, persistencia local y validación de stock.
- Domingo: diseño adaptable y pruebas del flujo de compra.

## Semana 6: 12 al 18 de octubre — pedidos

- Lunes: checkout y selección de domicilio.
- Martes: retiro o delivery y forma de pago.
- Miércoles: creación transaccional del pedido.
- Jueves: panel de pedidos del cliente.
- Viernes: panel operativo de pedidos.
- Sábado: estados pendiente, preparación, listo, entrega y cancelación.
- Domingo: descuento/devolución de stock y pruebas integrales.

## Semana 7: 19 al 25 de octubre — promociones y facturación

- Lunes: API de promociones.
- Martes: pantallas de promociones.
- Miércoles: reglas de vigencia y composición.
- Jueves: aplicar promociones al carrito.
- Viernes: aplicar promociones y descuentos al pedido.
- Sábado: facturación y comprobante imprimible.
- Domingo: pruebas de precios, fechas, totales y facturas.

## Semana 8: 26 de octubre al 1 de noviembre — reportes

- Lunes: filtros por rango de fechas.
- Martes: reporte de ingresos y cantidad de pedidos.
- Miércoles: ranking de productos.
- Jueves: ranking de clientes.
- Viernes: reporte de costos y ganancias.
- Sábado: gráficos y exportación a Excel/PDF.
- Domingo: permisos, pruebas y correcciones de reportes.

## Semana 9: 2 al 8 de noviembre — cierre y presentación

- Lunes: generar 30 pedidos reproducibles en distintos estados.
- Martes: verificar todas las cantidades mínimas requeridas.
- Miércoles: corregir textos, navegación y diseño adaptable.
- Jueves: pruebas completas por cada rol.
- Viernes: documentar instalación, usuarios demo y ejecución.
- Sábado: simulacro de presentación y resolución de errores.
- Domingo: generar versión candidata final y respaldo completo.

## Cantidades de aceptación

- [ ] 15 clientes con sus usuarios.
- [ ] 5 empleados con sus usuarios.
- [ ] 40 insumos para elaborar.
- [ ] 15 insumos para venta al público.
- [ ] 15 productos manufacturados con recetas.
- [ ] 5 promociones vigentes o históricas.
- [ ] 30 pedidos distribuidos entre distintos estados.
