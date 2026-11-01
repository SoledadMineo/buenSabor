# Preparación del usuario para login local

`Usuario` ahora extiende `Authenticatable`. El atributo `password` usa el cast
`hashed` y se oculta, junto a `remember_token`, en la serialización del modelo.
Crear o editar contraseñas a través de Eloquent aplica el hash; las escrituras
directas con `DB::table` no ejecutan casts.

La migración `2026_09_20_000002_prepare_usuario_for_local_login` deja `authOId`
opcional, agrega `password` y `remember_token`, y exige un `username` único.
Detecta nombres duplicados antes de alterar la tabla. Conserva las cuentas
anteriores con contraseña nula: no inventa contraseñas compartidas.

La migración fue aplicada por Soledad el 12 de octubre de 2026. Las pruebas del
modelo usan un esquema final SQLite en memoria, porque alterar columnas con
`change()` en este SQLite requiere Doctrine DBAL. No prueban la migración completa.

Ejecutar desde backend: `php artisan test --filter=UsuarioTest`.

El controlador de login rechaza con un error genérico las cuentas con password
nulo ANTES de llamar a Auth::attempt. Valida campos, limita las solicitudes a
cinco por minuto por IP y regenera el identificador de sesión al ingresar.
Las cuentas nuevas requerirán contraseña en la validación del alta.

## Sesión del 12 de octubre de 2026: endpoint de login

Decisión: autenticación local mediante sesión y cookies; authOId queda opcional.
POST /api/login recibe JSON con username y password. Devuelve 200 con
`{"user":{"id":1,"username":"sole"}}`, 401 para credenciales incorrectas,
422 para campos inválidos y 429 al superar el límite de solicitudes.
No activa todavía la opción Recordarme.

La ruta vive en routes/web.php para utilizar sesión y protección CSRF aunque
su URL empiece con /api. No se debe duplicar en routes/api.php.
Para conectar React: obtener primero GET /sanctum/csrf-cookie, enviar cookies
con credentials: include y enviar el valor decodificado de XSRF-TOKEN en el
header X-XSRF-TOKEN al hacer POST. Usar Accept: application/json y
Content-Type: application/json. Frontend y backend deben usar el mismo hostname
(ambos localhost o ambos 127.0.0.1). GET /api/user permite consultar la sesión.
Si SANCTUM_STATEFUL_DOMAINS está definido en el entorno, debe incluir el host
y puerto del frontend; sustituye los valores predeterminados de configuración.

Verificación: php artisan test completó 15 pruebas y 55 aserciones. Incluye
login correcto, recuperación del usuario desde sesión, rechazo de credenciales,
campos obligatorios, límite de solicitudes, ruta privada y rechazo sin CSRF.
Los usuarios de prueba se crean en SQLite en memoria, no en la base local.
Pendiente: comprobar el flujo completo en navegador cuando exista el frontend.

## Sesión del 14 de octubre de 2026: cierre de sesión

Soledad agregó logout en AuthController. Se conectó POST /api/logout en
routes/web.php, con sesión y protección CSRF. Devuelve 200 y el mensaje
`Sesion cerrada.`; permite repetir la solicitud aunque ya no haya un usuario
autenticado, siempre que se envíe el token CSRF vigente.

Verificación: php artisan test completó 17 pruebas y 73 aserciones. Se comprobó
ingreso, acceso a /api/user, logout y posterior respuesta 401. También se
comprobó la eliminación de datos de sesión, renovación de identificador y
token CSRF, conservación de la contraseña y rechazo del logout sin CSRF.
Las pruebas usan SQLite en memoria; falta la verificación desde el navegador.

## Formulario React basado en el ejemplo de clase

Se agregó /login conservando useState, useNavigate, login, usuario/clave y
txtValidacion. AuthApi.ts transforma esos campos a username/password, obtiene
la cookie CSRF y envía cookies y X-XSRF-TOKEN. App consulta /api/user al cargar
y comparte el usuario con el formulario y el encabezado; Salir llama al logout.
No se guardan contraseñas ni autenticación en localStorage. El envío mediante
onSubmit permite usar Enter sin recargar. El menú móvil usa estado de React.

Verificación: npm.cmd run build completado. No se verificó el recorrido en
navegador ni se creó una cuenta local durante esta implementación.
Abrir http://127.0.0.1:5173/login con Laravel en http://127.0.0.1:8000.
AuthApi usa el hostname del navegador y puerto 8000; VITE_API_URL permite
configurar otra base URL. Mantener el mismo hostname para frontend y backend.

## Punto de retomada acordado con Soledad

Antes de avanzar con funcionalidades nuevas, revisar juntos y paso a paso lo
implementado en login y logout, tomando como referencia su formulario de clase:

1. AuthController.php: validación de credenciales, inicio y cierre de sesión.
2. routes/web.php: rutas de login/logout y por qué usan sesión y protección CSRF.
3. Login.tsx y AuthApi.ts: qué se conservó del ejemplo de clase y qué se adaptó
   para Laravel (campos, respuesta, cookies y token CSRF).
4. App.tsx y Encabezado.tsx: recuperación de sesión y botón Salir.
5. Preparar juntos una cuenta local con contraseña mediante Usuario (para
   aplicar el hash) y verificar en navegador: error de contraseña, ingreso
   correcto, recarga que conserva la sesión y salida que retira el acceso.

Estado: acceso básico implementado, con pruebas de backend y compilación del
frontend aprobadas. No considerar cerrado el flujo completo hasta comprobarlo
en navegador. El módulo de usuarios no está completo: falta su gestión y roles.
Los permisos por rol y la protección de las rutas de artículos siguen pendientes.

El rollback elimina las columnas de credenciales. Se bloquea si existen cuentas
sin authOId para no convertir esos valores a datos ficticios ni borrar cuentas.
