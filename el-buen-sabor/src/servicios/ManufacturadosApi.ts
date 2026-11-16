import { pedir, type Opcion } from "./InsumosApi";

export interface IngredienteReceta {
  articulo_insumo_id: number;
  cantidad: number;
}

export interface InsumoElaboracion {
  id: number;
  denominacion: string;
  precioCompra: number;
  unidad_medida_id: number;
}

export interface ManufacturadoDetalle {
  id: number;
  cantidad: number;
  articulo_insumo_id: number;
  articulo_insumo?: { id: number; denominacion: string; precioCompra: number; unidad_medida?: Opcion };
}

export interface ManufacturadoCompleto {
  id: number;
  denominacion: string;
  descripcion: string;
  precioVenta: number;
  precioCosto: number;
  tiempoEstimado: number;
  categoria_id: number;
  costo_calculado: number;
  stock_posible: number;
  disponible: boolean;
  detalles: ManufacturadoDetalle[];
}

export const getManufacturadoCompleto = (id: number) =>
  pedir<ManufacturadoCompleto>("GET", `manufacturados/${id}`);

export const getCategoriasManufacturado = () => pedir<Opcion[]>("GET", "categorias-manufacturado");

// Solo los insumos para elaborar pueden formar parte de una receta.
export const getInsumosElaboracion = () => pedir<InsumoElaboracion[]>("GET", "insumos?tipo=elaboracion");

export interface DatosManufacturado {
  denominacion: string;
  descripcion: string;
  precioVenta: number;
  tiempoEstimado: number;
  categoria_id: number;
  ingredientes: IngredienteReceta[];
  imagen?: File | null;
}

// Laravel recibe la receta como ingredientes[0][articulo_insumo_id], ingredientes[0][cantidad], ...
function aFormData(d: DatosManufacturado): FormData {
  const f = new FormData();
  f.append("denominacion", d.denominacion);
  f.append("descripcion", d.descripcion);
  f.append("precioVenta", String(d.precioVenta));
  f.append("tiempoEstimado", String(d.tiempoEstimado));
  f.append("categoria_id", String(d.categoria_id));
  d.ingredientes.forEach((i, n) => {
    f.append(`ingredientes[${n}][articulo_insumo_id]`, String(i.articulo_insumo_id));
    f.append(`ingredientes[${n}][cantidad]`, String(i.cantidad));
  });
  if (d.imagen) f.append("imagen", d.imagen);
  return f;
}

export const crearManufacturado = (d: DatosManufacturado) =>
  pedir<ManufacturadoCompleto>("POST", "manufacturados", aFormData(d));

// Con archivos, Laravel necesita POST + _method=PUT.
export const actualizarManufacturado = (id: number, d: DatosManufacturado) => {
  const f = aFormData(d);
  f.append("_method", "PUT");
  return pedir<ManufacturadoCompleto>("POST", `manufacturados/${id}`, f);
};

export const bajaManufacturado = (id: number) => pedir("DELETE", `manufacturados/${id}`);
export const restaurarManufacturado = (id: number) => pedir("POST", `manufacturados/${id}/restaurar`);
