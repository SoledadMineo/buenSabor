import type ArticuloAdmin from "../entidades/ArticuloAdmin";

export const API = import.meta.env.VITE_API_URL ?? `http://${window.location.hostname}:8000`;

export interface Sucursal {
  id: number;
  nombre: string;
}

export interface StockSucursal {
  id: number;
  sucursal_empresa_id: number;
  articulo_insumo_id: number;
  stockActual: number;
  stockMinimo: number;
  stockMaximo: number;
  sucursal_empresa?: Sucursal;
}

export interface Opcion {
  id: number;
  denominacion: string;
}

async function csrf(): Promise<string> {
  const r = await fetch(`${API}/sanctum/csrf-cookie`, {
    credentials: "include",
    headers: { Accept: "application/json" },
  });
  if (!r.ok) throw new Error("No se pudo preparar la solicitud segura");
  const c = document.cookie.split("; ").find((i) => i.startsWith("XSRF-TOKEN="));
  if (!c) throw new Error("No se encontró la cookie de seguridad");
  return decodeURIComponent(c.substring("XSRF-TOKEN=".length));
}

// Primer mensaje de error útil que devuelve Laravel (validación o negocio).
async function mensajeDeError(r: Response): Promise<string> {
  try {
    const e = await r.json();
    if (e.errors) return (Object.values(e.errors).flat() as string[])[0];
    if (e.message) return e.message;
  } catch {
    /* sin cuerpo JSON */
  }
  return "Error en el servidor";
}

export async function pedir<T>(method: string, path: string, body?: unknown): Promise<T> {
  const headers: Record<string, string> = { Accept: "application/json" };
  let cuerpo: BodyInit | undefined;
  if (method !== "GET") {
    headers["X-XSRF-TOKEN"] = await csrf();
    if (body instanceof FormData) {
      cuerpo = body;
    } else if (body !== undefined) {
      headers["Content-Type"] = "application/json";
      cuerpo = JSON.stringify(body);
    }
  }
  const r = await fetch(`${API}/api/${path}`, { method, credentials: "include", headers, body: cuerpo });
  if (!r.ok) throw new Error(await mensajeDeError(r));
  return (await r.json()) as T;
}

export interface FiltrosAdmin {
  tipo?: "elaboracion" | "venta";
  buscar?: string;
  baja?: boolean;
}

export const listarArticulosAdmin = (f: FiltrosAdmin = {}) => {
  const q = new URLSearchParams();
  if (f.tipo) q.set("tipo", f.tipo);
  if (f.buscar) q.set("buscar", f.buscar);
  if (f.baja) q.set("baja", "1");
  return pedir<ArticuloAdmin[]>("GET", `articulos/admin?${q}`);
};

export const bajaInsumo = (id: number) => pedir("DELETE", `insumos/${id}`);
export const restaurarInsumo = (id: number) => pedir("POST", `insumos/${id}/restaurar`);

// Con archivos, Laravel necesita POST + _method=PUT.
export const actualizarInsumo = (id: number, datos: FormData) => {
  datos.append("_method", "PUT");
  return pedir("POST", `insumos/${id}`, datos);
};

export const listarSucursales = () => pedir<Sucursal[]>("GET", "sucursales");
export const getStockInsumo = (id: number) => pedir<StockSucursal[]>("GET", `insumos/${id}/stock`);
export const definirStock = (id: number, d: Omit<StockSucursal, "id" | "articulo_insumo_id" | "sucursal_empresa">) =>
  pedir<StockSucursal>("PUT", `insumos/${id}/stock`, d);
export const ajustarStock = (id: number, sucursal_empresa_id: number, cantidad: number) =>
  pedir<StockSucursal>("POST", `insumos/${id}/stock/ajuste`, { sucursal_empresa_id, cantidad });
