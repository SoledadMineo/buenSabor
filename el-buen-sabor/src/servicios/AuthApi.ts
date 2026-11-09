export interface UsuarioSesion {
  id: number;
  username: string;
  rol: "admin" | "empleado" | "cliente" | null;
}

const API = import.meta.env.VITE_API_URL ?? `http://${window.location.hostname}:8000`;

async function prepararCsrf(): Promise<string> {
  const response = await fetch(`${API}/sanctum/csrf-cookie`, {
    credentials: "include",
    headers: { Accept: "application/json" },
  });
  if (!response.ok) throw new Error("No se pudo preparar el acceso. Intente nuevamente.");
  const cookie = document.cookie.split("; ").find((item) => item.startsWith("XSRF-TOKEN="));
  if (!cookie) throw new Error("No se pudo obtener la cookie de seguridad.");
  return decodeURIComponent(cookie.substring("XSRF-TOKEN=".length));
}

async function enviar(path: string, datos = {}) {
  const token = await prepararCsrf();
  const response = await fetch(`${API}/api/${path}`, {
    method: "POST",
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-XSRF-TOKEN": token,
    },
    body: JSON.stringify(datos),
  });
  if (!response.ok) {
    if (response.status === 401) throw new Error("Usuario y/o clave incorrectas.");
    if (response.status === 429) throw new Error("Demasiados intentos. Espere un minuto.");
    if (response.status === 419) throw new Error("La sesion vencio. Intente nuevamente.");
    if (response.status === 422) throw new Error("Revise el usuario y la clave ingresados.");
    throw new Error("Error en el servidor. Intente mas tarde.");
  }
  return response;
}

export async function iniciarSesion(username: string, password: string): Promise<UsuarioSesion> {
  const response = await enviar("login", { username, password });
  const data = await response.json();
  return data.user;
}

export async function consultarSesion(): Promise<UsuarioSesion | null> {
  const response = await fetch(`${API}/api/user`, {
    credentials: "include",
    headers: { Accept: "application/json" },
  });
  if (response.status === 401) return null;
  if (!response.ok) throw new Error("No se pudo consultar la sesion.");
  return await response.json();
}

export async function cerrarSesion(): Promise<void> {
  await enviar("logout");
}
