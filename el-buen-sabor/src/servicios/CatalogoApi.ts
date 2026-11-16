import { API, pedir } from "./InsumosApi";
import type { ItemCarrito } from "../contexto/carrito";

export interface ProductoCatalogo {
  tipo: "insumo" | "manufacturado";
  id: number;
  denominacion: string;
  descripcion: string;
  precioVenta: number;
  categoria: string;
  imagen: string | null;
  // Unidades que se pueden vender ahora (porciones posibles o stock).
  disponibles: number;
}

interface InsumoApi {
  id: number;
  denominacion: string;
  precioVenta: number;
  stock_total: number | null;
  categoria?: { denominacion: string };
  imagen?: { denominacion: string } | null;
}

interface ManufacturadoApi {
  id: number;
  denominacion: string;
  descripcion: string;
  precioVenta: number;
  stock_posible: number;
  categoria?: { denominacion: string };
  imagenes?: { denominacion: string }[];
}

export const urlImagen = (ruta: string | null | undefined) => (ruta ? `${API}/storage/${ruta}` : null);

// Catálogo para el cliente: productos manufacturados e insumos de venta directa (bebidas, postres).
// Los insumos para elaborar no se muestran.
export async function getCatalogo(): Promise<ProductoCatalogo[]> {
  const [manufacturados, insumos] = await Promise.all([
    pedir<ManufacturadoApi[]>("GET", "manufacturados"),
    pedir<InsumoApi[]>("GET", "insumos?tipo=venta"),
  ]);

  return [
    ...manufacturados.map<ProductoCatalogo>((m) => ({
      tipo: "manufacturado",
      id: m.id,
      denominacion: m.denominacion,
      descripcion: m.descripcion,
      precioVenta: m.precioVenta,
      categoria: m.categoria?.denominacion ?? "Otros",
      imagen: urlImagen(m.imagenes?.[0]?.denominacion),
      disponibles: m.stock_posible,
    })),
    ...insumos.map<ProductoCatalogo>((i) => ({
      tipo: "insumo",
      id: i.id,
      denominacion: i.denominacion,
      descripcion: "",
      precioVenta: i.precioVenta,
      categoria: i.categoria?.denominacion ?? "Otros",
      imagen: urlImagen(i.imagen?.denominacion),
      disponibles: i.stock_total ?? 0,
    })),
  ];
}

export type EstadoLinea = "ok" | "stock_insuficiente" | "sin_stock" | "no_disponible";

export interface LineaValidada {
  tipo: "insumo" | "manufacturado";
  id: number;
  denominacion: string | null;
  precioVenta: number;
  cantidad: number;
  cantidad_ok: number;
  stock_max: number;
  estado: EstadoLinea;
}

export interface ResultadoValidacion {
  items: LineaValidada[];
  total: number;
  puede_continuar: boolean;
}

export const validarCarrito = (items: ItemCarrito[]) =>
  pedir<ResultadoValidacion>("POST", "carrito/validar", {
    items: items.map((i) => ({ tipo: i.tipo, id: i.id, cantidad: i.cantidad })),
  });
