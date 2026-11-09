import type ArticuloAdmin from "../entidades/ArticuloAdmin";
import type ArticuloInsumo from "../entidades/ArticuloInsumo";
import type ArticuloManufacturado from "../entidades/ArticuloManufacturado";

const API = import.meta.env.VITE_API_URL ?? `http://${window.location.hostname}:8000`;

// Obtener lista de insumos
export const getInsumosJSONFetch = async (): Promise<ArticuloInsumo[]> => {
    try {
        const response = await fetch("http://127.0.0.1:8000/api/insumos");
        if (!response.ok) {
            throw new Error("Error al obtener insumos");
        }
        const data = await response.json();
        return data as ArticuloInsumo[];
    } catch (error) {
        console.error("getInsumosJSONFetch:", error);
        return [];
    }
};

// Obtener artículos manufacturados
export const getManufacturadosJSONFetch = async (): Promise<ArticuloManufacturado[]> => {
    try {
        const response = await fetch("http://127.0.0.1:8000/api/manufacturados");
        if (!response.ok) {
            throw new Error("Error al obtener manufacturados");
        }
        const data = await response.json();
        return data as ArticuloManufacturado[];
    } catch (error) {
        console.error("getManufacturadosJSONFetch:", error);
        return [];
    }
};

export const getInsumoById = async (id: number): Promise<ArticuloInsumo> => {
  const response = await fetch(`http://127.0.0.1:8000/api/insumos/${id}`);
  return await response.json();
};

export const getManufacturadoById = async (id: number): Promise<ArticuloManufacturado> => {
  const response = await fetch(`http://127.0.0.1:8000/api/manufacturados/${id}`);
  return await response.json();
};

export const getArticulosAdmin = async (): Promise<ArticuloAdmin[]> => {
  const response = await fetch(`${API}/api/articulos/admin`, { credentials: "include" });
  if (!response.ok) throw new Error("No se pudieron cargar los artículos administrativos");
  return await response.json() as ArticuloAdmin[];
};

export const getUnidadesMedida = async () => {
  const res = await fetch("http://127.0.0.1:8000/api/unidades-medida");
  return await res.json();
};

export const getCategoriasArticulo = async () => {
  try {
    const response = await fetch("http://127.0.0.1:8000/api/categorias-articulo");
    if (!response.ok) {
      throw new Error("Error al obtener categorías");
    }
    return await response.json();
  } catch (error) {
    console.error("getCategoriasArticulo:", error);
    return [];
  }
};

export const saveInsumo = async (data: FormData) => {
  const csrfResponse = await fetch(`${API}/sanctum/csrf-cookie`, {
    credentials: "include",
    headers: { Accept: "application/json" },
  });
  if (!csrfResponse.ok) throw new Error("No se pudo preparar la solicitud segura");
  const csrfCookie = document.cookie.split("; ").find((item) => item.startsWith("XSRF-TOKEN="));
  if (!csrfCookie) throw new Error("No se encontró la cookie de seguridad");

  const response = await fetch(`${API}/api/insumos`, {
    method: "POST",
    credentials: "include",
    headers: { "X-XSRF-TOKEN": decodeURIComponent(csrfCookie.substring("XSRF-TOKEN=".length)), Accept: "application/json" },
    body: data, // 👈 SIN headers
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || "Error al guardar insumo");
  }

  return await response.json();
};

