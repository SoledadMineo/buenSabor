import { createContext, useContext } from "react";

export type TipoArticulo = "insumo" | "manufacturado";

export interface ItemCarrito {
  tipo: TipoArticulo;
  id: number;
  denominacion: string;
  precioVenta: number;
  imagen: string | null;
  cantidad: number;
}

export interface CarritoContexto {
  items: ItemCarrito[];
  cantidadTotal: number;
  total: number;
  // Suma unidades; si el artículo ya está en el carrito, acumula la cantidad.
  agregar: (item: Omit<ItemCarrito, "cantidad">, cantidad?: number) => void;
  cambiarCantidad: (tipo: TipoArticulo, id: number, cantidad: number) => void;
  quitar: (tipo: TipoArticulo, id: number) => void;
  vaciar: () => void;
  // Reemplaza el contenido (por ejemplo, tras validar contra el servidor).
  reemplazar: (items: ItemCarrito[]) => void;
}

export const CarritoCtx = createContext<CarritoContexto | null>(null);

export function useCarrito(): CarritoContexto {
  const ctx = useContext(CarritoCtx);
  if (!ctx) throw new Error("useCarrito debe usarse dentro de CarritoProvider");
  return ctx;
}
