import { useCallback, useEffect, useMemo, useState, type ReactNode } from "react";
import { CarritoCtx, type CarritoContexto, type ItemCarrito, type TipoArticulo } from "./carrito";

const CLAVE = "carrito-el-buen-sabor";
const MAX_POR_ITEM = 999;

// Lee el carrito guardado e ignora datos dañados o con formato inesperado.
function leerGuardado(): ItemCarrito[] {
  try {
    const datos = JSON.parse(localStorage.getItem(CLAVE) ?? "[]");
    if (!Array.isArray(datos)) return [];
    return datos.filter(
      (i): i is ItemCarrito =>
        (i?.tipo === "insumo" || i?.tipo === "manufacturado") &&
        Number.isInteger(i.id) &&
        typeof i.denominacion === "string" &&
        typeof i.precioVenta === "number" &&
        Number.isInteger(i.cantidad) &&
        i.cantidad > 0,
    );
  } catch {
    return [];
  }
}

const mismo = (i: ItemCarrito, tipo: TipoArticulo, id: number) => i.tipo === tipo && i.id === id;

export function CarritoProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<ItemCarrito[]>(leerGuardado);

  // Persistencia local: el carrito sobrevive a recargar la página.
  useEffect(() => {
    localStorage.setItem(CLAVE, JSON.stringify(items));
  }, [items]);

  // Mantiene sincronizadas varias pestañas abiertas.
  useEffect(() => {
    const alCambiar = (e: StorageEvent) => {
      if (e.key === CLAVE) setItems(leerGuardado());
    };
    window.addEventListener("storage", alCambiar);
    return () => window.removeEventListener("storage", alCambiar);
  }, []);

  const agregar = useCallback<CarritoContexto["agregar"]>((item, cantidad = 1) => {
    setItems((actual) => {
      const existente = actual.find((i) => mismo(i, item.tipo, item.id));
      if (!existente) return [...actual, { ...item, cantidad: Math.min(cantidad, MAX_POR_ITEM) }];
      return actual.map((i) =>
        mismo(i, item.tipo, item.id)
          ? { ...i, precioVenta: item.precioVenta, cantidad: Math.min(i.cantidad + cantidad, MAX_POR_ITEM) }
          : i,
      );
    });
  }, []);

  const cambiarCantidad = useCallback<CarritoContexto["cambiarCantidad"]>((tipo, id, cantidad) => {
    setItems((actual) =>
      cantidad < 1
        ? actual.filter((i) => !mismo(i, tipo, id))
        : actual.map((i) => (mismo(i, tipo, id) ? { ...i, cantidad: Math.min(cantidad, MAX_POR_ITEM) } : i)),
    );
  }, []);

  const quitar = useCallback<CarritoContexto["quitar"]>((tipo, id) => {
    setItems((actual) => actual.filter((i) => !mismo(i, tipo, id)));
  }, []);

  const vaciar = useCallback(() => setItems([]), []);
  const reemplazar = useCallback((nuevos: ItemCarrito[]) => setItems(nuevos), []);

  const valor = useMemo<CarritoContexto>(
    () => ({
      items,
      cantidadTotal: items.reduce((n, i) => n + i.cantidad, 0),
      total: items.reduce((t, i) => t + i.cantidad * i.precioVenta, 0),
      agregar,
      cambiarCantidad,
      quitar,
      vaciar,
      reemplazar,
    }),
    [items, agregar, cambiarCantidad, quitar, vaciar, reemplazar],
  );

  return <CarritoCtx.Provider value={valor}>{children}</CarritoCtx.Provider>;
}
