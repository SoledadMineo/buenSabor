# Plan de finalización de El Buen Sabor

Período objetivo: 6 de septiembre al 8 de noviembre de 2026.

Disponibilidad estimada:

- Lunes a viernes: 2 horas por día.
- Sábados y domingos: 4 a 6 horas por día.
- Total: 18 a 22 horas semanales.

## Reglas de trabajo

Cada jornada comienza revisando la meta y termina con una prueba, un commit y una
nota breve de lo que queda pendiente. Un módulo solo se considera terminado si
tiene interfaz, API, validaciones, persistencia y prueba del flujo principal.

## Inicio: domingo 6 de septiembre (4 a 6 h)

- [x] Auditar frontend, backend y datos existentes.
- [x] Registrar los requisitos y cantidades mínimas.
- [x] Corregir la compilación inicial del frontend.
- [ ] Crear un respaldo exportable de la base MySQL actual.
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
