import { useCallback, useEffect, useState } from "react";
import { Link } from "react-router-dom";
import Alert from "react-bootstrap/Alert";
import type ArticuloAdmin from "../entidades/ArticuloAdmin";
import {
  bajaInsumo,
  listarArticulosAdmin,
  restaurarInsumo,
} from "../servicios/InsumosApi";
import { bajaManufacturado, restaurarManufacturado } from "../servicios/ManufacturadosApi";

type Vista = "todos" | "elaboracion" | "venta" | "manufacturado" | "baja";

const PESTANIAS: { valor: Vista; texto: string }[] = [
  { valor: "todos", texto: "Todos" },
  { valor: "elaboracion", texto: "Insumos para elaborar" },
  { valor: "venta", texto: "Insumos de venta" },
  { valor: "manufacturado", texto: "Manufacturados" },
  { valor: "baja", texto: "Dados de baja" },
];

function AdminArticulos() {
  const [articulos, setArticulos] = useState<ArticuloAdmin[]>([]);
  const [vista, setVista] = useState<Vista>("todos");
  const [buscar, setBuscar] = useState("");
  const [mensaje, setMensaje] = useState<{ tipo: "success" | "danger"; texto: string } | null>(null);

  const cargar = useCallback(async () => {
    try {
      const datos = await listarArticulosAdmin({
        tipo: vista === "elaboracion" || vista === "venta" ? vista : undefined,
        buscar: buscar.trim() || undefined,
        baja: vista === "baja",
      });
      setArticulos(vista === "manufacturado" ? datos.filter((a) => a.tipo === "manufacturado") : datos);
    } catch (e) {
      setMensaje({ tipo: "danger", texto: (e as Error).message });
    }
  }, [vista, buscar]);

  // Espera un instante al escribir para no consultar en cada tecla.
  useEffect(() => {
    const t = setTimeout(cargar, 250);
    return () => clearTimeout(t);
  }, [cargar]);

  const dar = async (a: ArticuloAdmin, accion: "baja" | "restaurar") => {
    if (accion === "baja" && !window.confirm(`¿Dar de baja "${a.denominacion}"? Podrá restaurarlo luego.`)) return;
    try {
      const esInsumo = a.tipo === "insumo";
      if (accion === "baja") await (esInsumo ? bajaInsumo(a.id!) : bajaManufacturado(a.id!));
      else await (esInsumo ? restaurarInsumo(a.id!) : restaurarManufacturado(a.id!));
      setMensaje({ tipo: "success", texto: accion === "baja" ? "Dado de baja." : "Restaurado." });
      cargar();
    } catch (e) {
      setMensaje({ tipo: "danger", texto: (e as Error).message });
    }
  };

  return (
    <div className="container mt-4">
      <div className="d-flex justify-content-between align-items-center mb-3">
        <h2>Administrar Artículos</h2>
        <Link
          to="/admin/articulos/nuevo"
          className="btn"
          style={{ backgroundColor: "#a68787", color: "white", width: "220px" }}
        >
          + Nuevo artículo
        </Link>
      </div>

      {mensaje && (
        <Alert variant={mensaje.tipo} dismissible onClose={() => setMensaje(null)}>
          {mensaje.texto}
        </Alert>
      )}

      <ul className="nav nav-tabs mb-3">
        {PESTANIAS.map((p) => (
          <li className="nav-item" key={p.valor}>
            <button
              className={`nav-link ${vista === p.valor ? "active" : ""}`}
              onClick={() => setVista(p.valor)}
            >
              {p.texto}
            </button>
          </li>
        ))}
      </ul>

      <input
        className="form-control w-25 mb-3"
        placeholder="Buscar por nombre..."
        value={buscar}
        onChange={(e) => setBuscar(e.target.value)}
        disabled={vista === "manufacturado"}
      />

      <table className="table table-hover align-middle">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Unidad</th>
            <th>Precio venta</th>
            <th>Stock</th>
            <th>Tipo</th>
            <th style={{ width: "280px" }}>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {articulos.map((a) => (
            <tr key={`${a.tipo}-${a.id}`}>
              <td>{a.denominacion}</td>
              <td>{a.categoria?.denominacion ?? "-"}</td>
              <td>{a.unidad_medida?.denominacion ?? "-"}</td>
              <td>${a.precioVenta}</td>
              <td>
                {a.tipo === "insumo" ? (
                  <>
                    {a.stock_total ?? 0}{" "}
                    {a.stock_bajo && <span className="badge bg-danger">Stock bajo</span>}
                  </>
                ) : (
                  <>
                    {a.stock_posible ?? 0} porciones{" "}
                    {!a.stock_posible && <span className="badge bg-danger">Sin stock</span>}
                  </>
                )}
              </td>
              <td>
                {a.tipo === "manufacturado"
                  ? "Manufacturado"
                  : a.esParaElaborar
                    ? "Para elaborar"
                    : "Venta directa"}
              </td>
              <td>
                <div className="d-flex gap-2">
                  {a.deleted_at ? (
                    <button className="btn btn-success btn-sm" onClick={() => dar(a, "restaurar")}>
                      Restaurar
                    </button>
                  ) : (
                    <>
                      <Link
                        to={a.tipo === "insumo" ? `/admin/insumos/${a.id}/editar` : `/admin/manufacturados/${a.id}/editar`}
                        className="btn btn-dark btn-sm"
                      >
                        Editar
                      </Link>
                      {a.tipo === "insumo" && (
                        <Link to={`/admin/insumos/${a.id}/stock`} className="btn btn-secondary btn-sm">
                          Stock
                        </Link>
                      )}
                      <button className="btn btn-danger btn-sm" onClick={() => dar(a, "baja")}>
                        Dar de baja
                      </button>
                    </>
                  )}
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {articulos.length === 0 && <p className="text-muted">No hay artículos para mostrar</p>}
    </div>
  );
}

export default AdminArticulos;
