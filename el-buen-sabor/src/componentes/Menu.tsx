import { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import sinimagen from "../assets/sinimagen.png";
import { useCarrito } from "../contexto/carrito";
import { getCatalogo, type ProductoCatalogo } from "../servicios/CatalogoApi";

function Menu() {
  const { items, agregar } = useCarrito();
  const [productos, setProductos] = useState<ProductoCatalogo[]>([]);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState("");
  const [categoria, setCategoria] = useState("Todas");
  const [buscar, setBuscar] = useState("");
  const [aviso, setAviso] = useState("");

  useEffect(() => {
    getCatalogo()
      .then(setProductos)
      .catch(() => setError("No se pudo cargar el menú. Intente nuevamente."))
      .finally(() => setCargando(false));
  }, []);

  const categorias = useMemo(
    () => ["Todas", ...Array.from(new Set(productos.map((p) => p.categoria))).sort()],
    [productos],
  );

  const visibles = productos.filter(
    (p) =>
      (categoria === "Todas" || p.categoria === categoria) &&
      p.denominacion.toLowerCase().includes(buscar.trim().toLowerCase()),
  );

  const enCarrito = (p: ProductoCatalogo) =>
    items.find((i) => i.tipo === p.tipo && i.id === p.id)?.cantidad ?? 0;

  const comprar = (p: ProductoCatalogo) => {
    if (enCarrito(p) >= p.disponibles) {
      setAviso(`No hay más unidades de "${p.denominacion}" disponibles.`);
      return;
    }
    setAviso("");
    agregar({
      tipo: p.tipo,
      id: p.id,
      denominacion: p.denominacion,
      precioVenta: p.precioVenta,
      imagen: p.imagen,
    });
  };

  return (
    <div className="container py-4">
      <h2>Nuestros platos</h2>

      <div className="d-flex flex-wrap gap-2 align-items-center my-3">
        {categorias.map((c) => (
          <button
            key={c}
            type="button"
            className={`btn btn-sm ${categoria === c ? "btn-dark" : "btn-outline-dark"}`}
            onClick={() => setCategoria(c)}
          >
            {c}
          </button>
        ))}
        <input
          className="form-control ms-auto"
          style={{ maxWidth: 260 }}
          placeholder="Buscar..."
          value={buscar}
          onChange={(e) => setBuscar(e.target.value)}
        />
      </div>

      {error && <p className="alert alert-danger">{error}</p>}
      {aviso && <p className="alert alert-warning" role="status">{aviso}</p>}
      {cargando && <p role="status">Cargando menú...</p>}

      <div className="d-flex flex-wrap gap-4 mt-4 justify-content-center">
        {visibles.map((p) => {
          const agotado = p.disponibles <= 0;
          return (
            <div
              className="card"
              style={{ width: "15rem", backgroundColor: "#e5dddd", opacity: agotado ? 0.7 : 1 }}
              key={`${p.tipo}-${p.id}`}
            >
              <img
                src={p.imagen ?? sinimagen}
                className="card-img-top"
                alt={p.denominacion}
                style={{ height: 160, objectFit: "cover" }}
              />
              <div className="card-body d-flex flex-column">
                <h5 className="card-title">{p.denominacion}</h5>
                {p.descripcion && <p className="card-text small">{p.descripcion}</p>}
                <p className="fw-bold mb-1">
                  {p.precioVenta.toLocaleString("es-AR", { style: "currency", currency: "ARS" })}
                </p>
                <p className="small mb-2">
                  {agotado ? (
                    <span className="badge bg-danger">Sin stock</span>
                  ) : p.disponibles <= 5 ? (
                    <span className="badge bg-warning text-dark">Últimas {p.disponibles}</span>
                  ) : (
                    <span className="badge bg-success">Disponible</span>
                  )}
                  {enCarrito(p) > 0 && <span className="ms-2 text-muted">En el carrito: {enCarrito(p)}</span>}
                </p>
                <div className="d-flex gap-2 justify-content-center mt-auto">
                  <button
                    type="button"
                    className="btn"
                    style={{ backgroundColor: "#d53434", color: "white" }}
                    disabled={agotado}
                    onClick={() => comprar(p)}
                  >
                    AGREGAR
                  </button>
                  <Link
                    to={`/detalle/${p.tipo}/${p.id}`}
                    className="btn"
                    style={{ backgroundColor: "#747474", color: "white" }}
                  >
                    DETALLE
                  </Link>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {!cargando && !error && visibles.length === 0 && (
        <p className="text-muted text-center mt-4">No hay productos que coincidan.</p>
      )}
    </div>
  );
}

export default Menu;
