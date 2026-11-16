import { Link, useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import sinimagen from "../assets/sinimagen.png";
import { useCarrito } from "../contexto/carrito";
import { getCatalogo, type ProductoCatalogo } from "../servicios/CatalogoApi";
import { getManufacturadoCompleto, type ManufacturadoCompleto } from "../servicios/ManufacturadosApi";

function DetalleProducto() {
  const { id, tipo } = useParams();
  const { items, agregar } = useCarrito();
  const [producto, setProducto] = useState<ProductoCatalogo | null>(null);
  const [receta, setReceta] = useState<ManufacturadoCompleto | null>(null);
  const [estado, setEstado] = useState<"cargando" | "listo" | "no_encontrado">("cargando");

  useEffect(() => {
    if (!id || !tipo) return;
    let activo = true;
    getCatalogo()
      .then((lista) => {
        if (!activo) return;
        const p = lista.find((x) => x.tipo === tipo && x.id === Number(id)) ?? null;
        setProducto(p);
        setEstado(p ? "listo" : "no_encontrado");
      })
      .catch(() => activo && setEstado("no_encontrado"));
    if (tipo === "manufacturado") {
      getManufacturadoCompleto(Number(id)).then((m) => activo && setReceta(m)).catch(() => undefined);
    }
    return () => {
      activo = false;
    };
  }, [id, tipo]);

  if (estado === "cargando") return <p className="container py-4" role="status">Cargando...</p>;
  if (!producto) {
    return (
      <div className="container py-4">
        <p>Este producto no está disponible.</p>
        <Link to="/" className="btn btn-dark">Volver al menú</Link>
      </div>
    );
  }

  const enCarrito = items.find((i) => i.tipo === producto.tipo && i.id === producto.id)?.cantidad ?? 0;
  const sinMas = enCarrito >= producto.disponibles;

  return (
    <div className="container py-4">
      <h2>{producto.denominacion}</h2>
      <p>Precio: {producto.precioVenta.toLocaleString("es-AR", { style: "currency", currency: "ARS" })}</p>
      <div className="card" style={{ width: "18rem", backgroundColor: "#e5dddd" }}>
        <img src={producto.imagen ?? sinimagen} className="card-img-top" alt={producto.denominacion} />
        <div className="card-body">
          <h5 className="card-title">{producto.denominacion}</h5>
          {producto.descripcion && <p className="card-text">{producto.descripcion}</p>}
          {receta && receta.detalles.length > 0 && (
            <>
              <p className="mb-1 fw-semibold">Ingredientes</p>
              <ul className="small">
                {receta.detalles.map((d) => (
                  <li key={d.id}>{d.articulo_insumo?.denominacion}</li>
                ))}
              </ul>
              <p className="small text-muted">Preparación: {receta.tiempoEstimado} min</p>
            </>
          )}
          <div className="d-flex gap-4 justify-content-center">
            <button
              type="button"
              className="btn"
              style={{ backgroundColor: "#d53434", color: "white" }}
              disabled={producto.disponibles <= 0 || sinMas}
              onClick={() =>
                agregar({
                  tipo: producto.tipo,
                  id: producto.id,
                  denominacion: producto.denominacion,
                  precioVenta: producto.precioVenta,
                  imagen: producto.imagen,
                })
              }
            >
              {producto.disponibles <= 0 ? "SIN STOCK" : "AGREGAR"}
            </button>
            <Link to="/" className="btn" style={{ backgroundColor: "#747474", color: "white" }}>
              VOLVER
            </Link>
          </div>
          {enCarrito > 0 && <p className="small text-center mt-2 mb-0">En el carrito: {enCarrito}</p>}
        </div>
      </div>
    </div>
  );
}

export default DetalleProducto;
