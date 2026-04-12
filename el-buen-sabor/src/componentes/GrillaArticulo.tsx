import { useEffect, useState } from "react";
import { getArticulosAdmin } from "../servicios/FuncionesApi";
import { Link } from "react-router-dom";
import type ArticuloAdmin from "../entidades/ArticuloAdmin";

function AdminArticulos() {
  const [articulos, setArticulos] = useState<ArticuloAdmin[]>([]);
  const [filtro, setFiltro] = useState<"todos" | "insumo" | "manufacturado">(
    "todos",
  );

  useEffect(() => {
    getArticulosAdmin().then(setArticulos).catch(console.error);
  }, []);

  const articulosFiltrados =
    filtro === "todos" ? articulos : articulos.filter((a) => a.tipo === filtro);

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

      {/* Filtro */}
      <select
        className="form-select w-25 mb-3"
        value={filtro}
        onChange={(e) => setFiltro(e.target.value as any)}
      >
        <option value="todos">Todos</option>
        <option value="insumo">Insumos</option>
        <option value="manufacturado">Manufacturados</option>
      </select>

      {/* Tabla */}
      <table className="table table-hover">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Tipo</th>
            <th style={{ width: "200px" }}>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {articulosFiltrados.map((a) => (
            <tr key={`${a.tipo}-${a.id}`}>
              <td>{a.denominacion}</td>
              <td>${a.precioVenta}</td>
              <td className="text-capitalize">{a.tipo}</td>
              <td>
                <div
                  className="d-flex gap-4 justify-content-center"
                  style={{ width: "220px" }}
                >
                  <Link
                    to={`/admin/articulos/editar/${a.tipo}/${a.id}`}
                    className="btn w-100"
                    style={{ backgroundColor: "#323131", color: "white" }}
                  >
                    Editar
                  </Link>

                  <Link
                    to={`/admin/articulos/editar/${a.tipo}/${a.id}`}
                    className="btn w-100"
                    style={{ backgroundColor: "#d53434", color: "white" }}
                  >
                    Eliminar
                  </Link>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {articulosFiltrados.length === 0 && (
        <p className="text-muted">No hay artículos para mostrar</p>
      )}
    </div>
  );
}

export default AdminArticulos;
