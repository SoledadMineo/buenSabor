import { useCallback, useEffect, useState } from "react";
import { Link } from "react-router-dom";
import sinimagen from "../assets/sinimagen.png";
import { useCarrito, type ItemCarrito } from "../contexto/carrito";
import { validarCarrito, type LineaValidada, type ResultadoValidacion } from "../servicios/CatalogoApi";

const dinero = (n: number) => n.toLocaleString("es-AR", { style: "currency", currency: "ARS" });

const MENSAJES: Record<string, string> = {
  stock_insuficiente: "Hay menos unidades que las pedidas.",
  sin_stock: "Sin stock.",
  no_disponible: "Este producto ya no está a la venta.",
};

function Carrito() {
  const { items, total, cambiarCantidad, quitar, vaciar, reemplazar } = useCarrito();
  const [resultado, setResultado] = useState<ResultadoValidacion | null>(null);
  const [error, setError] = useState("");
  const [revisando, setRevisando] = useState(false);
  const [avisos, setAvisos] = useState<string[]>([]);

  // Compara el carrito con el servidor: actualiza precios, quita lo que ya no se vende
  // y baja las cantidades al stock disponible.
  const revisar = useCallback(async (actuales: ItemCarrito[]) => {
    if (actuales.length === 0) {
      setResultado(null);
      return;
    }
    setRevisando(true);
    setError("");
    try {
      const r = await validarCarrito(actuales);
      const cambios: string[] = [];
      const ajustados = actuales.flatMap((item, n) => {
        const l = r.items[n];
        if (l.estado === "no_disponible" || l.cantidad_ok === 0) {
          cambios.push(`"${item.denominacion}" se quitó del carrito: ${MENSAJES[l.estado] ?? "no disponible"}`);
          return [];
        }
        if (l.cantidad_ok !== item.cantidad) {
          cambios.push(`"${item.denominacion}": se ajustó la cantidad a ${l.cantidad_ok} por falta de stock.`);
        }
        if (l.precioVenta !== item.precioVenta) {
          cambios.push(`"${item.denominacion}": el precio cambió a ${dinero(l.precioVenta)}.`);
        }
        return [{ ...item, cantidad: l.cantidad_ok, precioVenta: l.precioVenta }];
      });
      if (cambios.length > 0) {
        setAvisos(cambios);
        reemplazar(ajustados);
      }
      setResultado(r);
    } catch (e) {
      setError((e as Error).message || "No se pudo revisar el carrito.");
    } finally {
      setRevisando(false);
    }
  }, [reemplazar]);

  // Al abrir la pantalla se revisa una vez.
  useEffect(() => {
    void revisar(items);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const linea = (i: ItemCarrito): LineaValidada | undefined =>
    resultado?.items.find((l) => l.tipo === i.tipo && l.id === i.id);

  if (items.length === 0) {
    return (
      <div className="container py-5 text-center">
        <h2>Tu carrito está vacío</h2>
        {avisos.map((a) => <p key={a} className="alert alert-warning">{a}</p>)}
        <Link to="/" className="btn btn-dark mt-3">Ver el menú</Link>
      </div>
    );
  }

  return (
    <div className="container py-4">
      <h2>Tu carrito</h2>

      {error && <p className="alert alert-danger">{error}</p>}
      {avisos.map((a) => (
        <p key={a} className="alert alert-warning" role="status">{a}</p>
      ))}

      <table className="table align-middle">
        <thead>
          <tr>
            <th></th>
            <th>Producto</th>
            <th>Precio</th>
            <th style={{ width: 170 }}>Cantidad</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {items.map((i) => {
            const l = linea(i);
            const tope = l ? l.stock_max : 999;
            return (
              <tr key={`${i.tipo}-${i.id}`}>
                <td style={{ width: 70 }}>
                  <img src={i.imagen ?? sinimagen} alt="" width={60} height={60} style={{ objectFit: "cover", borderRadius: 8 }} />
                </td>
                <td>
                  {i.denominacion}
                  {l && l.estado !== "ok" && <div className="small text-danger">{MENSAJES[l.estado]}</div>}
                </td>
                <td>{dinero(i.precioVenta)}</td>
                <td>
                  <div className="input-group input-group-sm">
                    <button className="btn btn-outline-dark" aria-label="Restar una unidad"
                      onClick={() => cambiarCantidad(i.tipo, i.id, i.cantidad - 1)}>−</button>
                    <input
                      className="form-control text-center"
                      type="number"
                      min={1}
                      max={tope}
                      value={i.cantidad}
                      aria-label={`Cantidad de ${i.denominacion}`}
                      onChange={(e) => {
                        const n = Math.floor(Number(e.target.value));
                        if (Number.isFinite(n) && n >= 1) cambiarCantidad(i.tipo, i.id, Math.min(n, tope));
                      }}
                    />
                    <button className="btn btn-outline-dark" aria-label="Sumar una unidad"
                      disabled={i.cantidad >= tope}
                      onClick={() => cambiarCantidad(i.tipo, i.id, i.cantidad + 1)}>+</button>
                  </div>
                </td>
                <td>{dinero(i.precioVenta * i.cantidad)}</td>
                <td>
                  <button className="btn btn-sm btn-danger" onClick={() => quitar(i.tipo, i.id)}>Quitar</button>
                </td>
              </tr>
            );
          })}
        </tbody>
      </table>

      <div className="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <button className="btn btn-outline-dark" onClick={() => { vaciar(); setResultado(null); setAvisos([]); }}>
          Vaciar carrito
        </button>
        <div className="text-end">
          <h4 className="mb-2">Total: {dinero(total)}</h4>
          <div className="d-flex gap-2 justify-content-end">
            <button className="btn btn-secondary" disabled={revisando} onClick={() => void revisar(items)}>
              {revisando ? "Revisando..." : "Revisar stock y precios"}
            </button>
            {/* El pedido se completa en el paso siguiente (checkout). */}
            <button className="btn btn-success" disabled title="Disponible próximamente">
              Confirmar pedido
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Carrito;
