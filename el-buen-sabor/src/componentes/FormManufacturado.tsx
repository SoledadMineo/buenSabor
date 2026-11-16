import { useEffect, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import Alert from "react-bootstrap/Alert";
import type { Opcion } from "../servicios/InsumosApi";
import {
  actualizarManufacturado,
  crearManufacturado,
  getCategoriasManufacturado,
  getInsumosElaboracion,
  getManufacturadoCompleto,
  type InsumoElaboracion,
} from "../servicios/ManufacturadosApi";

interface LineaReceta {
  articulo_insumo_id: number;
  cantidad: string;
}

function FormManufacturado() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [denominacion, setDenominacion] = useState("");
  const [descripcion, setDescripcion] = useState("");
  const [precioVenta, setPrecioVenta] = useState("");
  const [tiempoEstimado, setTiempoEstimado] = useState("");
  const [categoriaId, setCategoriaId] = useState(0);
  const [receta, setReceta] = useState<LineaReceta[]>([]);
  const [imagen, setImagen] = useState<File | null>(null);

  const [categorias, setCategorias] = useState<Opcion[]>([]);
  const [insumos, setInsumos] = useState<InsumoElaboracion[]>([]);
  const [filtroInsumo, setFiltroInsumo] = useState("");
  const [mensaje, setMensaje] = useState<{ tipo: "success" | "danger" | "warning"; texto: string } | null>(null);
  const [guardando, setGuardando] = useState(false);

  useEffect(() => {
    getCategoriasManufacturado().then(setCategorias).catch(() => setMensaje({ tipo: "danger", texto: "No se pudieron cargar las categorías" }));
    getInsumosElaboracion().then(setInsumos).catch(() => setMensaje({ tipo: "danger", texto: "No se pudieron cargar los insumos" }));
  }, []);

  // Modo edición: carga el producto y su receta.
  useEffect(() => {
    if (!id) return;
    getManufacturadoCompleto(Number(id))
      .then((m) => {
        setDenominacion(m.denominacion);
        setDescripcion(m.descripcion);
        setPrecioVenta(String(m.precioVenta));
        setTiempoEstimado(String(m.tiempoEstimado));
        setCategoriaId(m.categoria_id);
        setReceta(m.detalles.map((d) => ({ articulo_insumo_id: d.articulo_insumo_id, cantidad: String(d.cantidad) })));
      })
      .catch(() => setMensaje({ tipo: "danger", texto: "No se pudo cargar el producto" }));
  }, [id]);

  const porId = useMemo(() => new Map(insumos.map((i) => [i.id, i])), [insumos]);

  const disponibles = insumos.filter(
    (i) =>
      !receta.some((l) => l.articulo_insumo_id === i.id) &&
      i.denominacion.toLowerCase().includes(filtroInsumo.trim().toLowerCase()),
  );

  const costo = receta.reduce(
    (total, l) => total + (Number(l.cantidad) || 0) * (porId.get(l.articulo_insumo_id)?.precioCompra ?? 0),
    0,
  );

  const agregar = (insumoId: number) => {
    setReceta([...receta, { articulo_insumo_id: insumoId, cantidad: "1" }]);
    setFiltroInsumo("");
  };

  const cambiarCantidad = (insumoId: number, cantidad: string) =>
    setReceta(receta.map((l) => (l.articulo_insumo_id === insumoId ? { ...l, cantidad } : l)));

  const quitar = (insumoId: number) => setReceta(receta.filter((l) => l.articulo_insumo_id !== insumoId));

  const validar = (): string => {
    if (!denominacion.trim()) return "La denominación es obligatoria";
    if (!descripcion.trim()) return "La descripción es obligatoria";
    if (Number(precioVenta) <= 0) return "El precio de venta debe ser mayor a 0";
    if (!Number.isInteger(Number(tiempoEstimado)) || Number(tiempoEstimado) < 1) return "El tiempo estimado debe ser de al menos 1 minuto";
    if (categoriaId === 0) return "Seleccione una categoría";
    if (receta.length === 0) return "Agregue al menos un ingrediente a la receta";
    if (receta.some((l) => !(Number(l.cantidad) > 0))) return "Todas las cantidades de la receta deben ser mayores a 0";
    return "";
  };

  const guardar = async () => {
    const error = validar();
    if (error) {
      setMensaje({ tipo: "warning", texto: error });
      return;
    }
    setGuardando(true);
    const datos = {
      denominacion: denominacion.trim(),
      descripcion: descripcion.trim(),
      precioVenta: Number(precioVenta),
      tiempoEstimado: Number(tiempoEstimado),
      categoria_id: categoriaId,
      ingredientes: receta.map((l) => ({ articulo_insumo_id: l.articulo_insumo_id, cantidad: Number(l.cantidad) })),
      imagen,
    };
    try {
      if (id) await actualizarManufacturado(Number(id), datos);
      else await crearManufacturado(datos);
      setMensaje({ tipo: "success", texto: "✅ Producto guardado correctamente" });
      setTimeout(() => navigate("/grilla"), 1200);
    } catch (e) {
      setMensaje({ tipo: "danger", texto: `❌ ${(e as Error).message}` });
      setGuardando(false);
    }
  };

  const dinero = (n: number) => n.toLocaleString("es-AR", { style: "currency", currency: "ARS" });

  return (
    <div className="container mt-4">
      {mensaje && <Alert variant={mensaje.tipo}>{mensaje.texto}</Alert>}
      <div className="card shadow">
        <div className="card-body">
          <label className="form-label fw-semibold">
            Denominación <span className="text-danger">*</span>
          </label>
          <input className="form-control mb-3" value={denominacion} onChange={(e) => setDenominacion(e.target.value)} />

          <label className="form-label fw-semibold">
            Descripción <span className="text-danger">*</span>
          </label>
          <textarea className="form-control mb-3" value={descripcion} onChange={(e) => setDescripcion(e.target.value)} />

          <div className="row">
            <div className="col-md-4">
              <label className="form-label fw-semibold">
                Categoría <span className="text-danger">*</span>
              </label>
              <select className="form-select mb-3" value={categoriaId} onChange={(e) => setCategoriaId(Number(e.target.value))}>
                <option value={0}>Seleccione una categoría</option>
                {categorias.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.denominacion}
                  </option>
                ))}
              </select>
            </div>
            <div className="col-md-4">
              <label className="form-label fw-semibold">
                Precio de venta <span className="text-danger">*</span>
              </label>
              <input type="number" min={0} className="form-control mb-3" value={precioVenta} onChange={(e) => setPrecioVenta(e.target.value)} />
            </div>
            <div className="col-md-4">
              <label className="form-label fw-semibold">
                Tiempo estimado (min) <span className="text-danger">*</span>
              </label>
              <input type="number" min={1} className="form-control mb-3" value={tiempoEstimado} onChange={(e) => setTiempoEstimado(e.target.value)} />
            </div>
          </div>

          <h5 className="mt-2">Receta</h5>

          <div className="border rounded p-3 mb-3 bg-light">
            <input
              className="form-control mb-2"
              placeholder="Buscar insumo para elaborar..."
              value={filtroInsumo}
              onChange={(e) => setFiltroInsumo(e.target.value)}
            />
            <div style={{ maxHeight: 160, overflowY: "auto" }}>
              {disponibles.slice(0, 30).map((i) => (
                <button key={i.id} type="button" className="btn btn-sm btn-outline-dark me-2 mb-2" onClick={() => agregar(i.id)}>
                  + {i.denominacion}
                </button>
              ))}
              {disponibles.length === 0 && <span className="text-muted small">No hay insumos que coincidan.</span>}
            </div>
          </div>

          {receta.length > 0 && (
            <table className="table table-sm align-middle">
              <thead>
                <tr>
                  <th>Ingrediente</th>
                  <th style={{ width: 140 }}>Cantidad</th>
                  <th>Costo</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {receta.map((l) => {
                  const insumo = porId.get(l.articulo_insumo_id);
                  return (
                    <tr key={l.articulo_insumo_id}>
                      <td>{insumo?.denominacion ?? `Insumo #${l.articulo_insumo_id}`}</td>
                      <td>
                        <input
                          type="number"
                          min={0}
                          step="any"
                          className="form-control form-control-sm"
                          value={l.cantidad}
                          onChange={(e) => cambiarCantidad(l.articulo_insumo_id, e.target.value)}
                        />
                      </td>
                      <td>{dinero((Number(l.cantidad) || 0) * (insumo?.precioCompra ?? 0))}</td>
                      <td>
                        <button type="button" className="btn btn-sm btn-danger" onClick={() => quitar(l.articulo_insumo_id)}>
                          Quitar
                        </button>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          )}

          <p className="fw-semibold">
            Costo estimado: {dinero(costo)}
            {Number(precioVenta) > 0 && costo > 0 && (
              <span className={Number(precioVenta) < costo ? "text-danger ms-3" : "text-success ms-3"}>
                {Number(precioVenta) < costo ? "Precio por debajo del costo" : `Ganancia: ${dinero(Number(precioVenta) - costo)}`}
              </span>
            )}
          </p>

          <div className="mb-3">
            <label className="form-label fw-semibold">Imagen del producto</label>
            <input
              type="file"
              className="form-control"
              accept="image/*"
              onChange={(e) => setImagen(e.target.files?.[0] ?? null)}
            />
          </div>
        </div>

        <div className="d-flex justify-content-end gap-2 m-2">
          <button className="btn btn-dark" onClick={() => navigate("/grilla")}>
            Cancelar
          </button>
          <button className="btn btn-success" onClick={guardar} disabled={guardando}>
            Guardar
          </button>
        </div>
      </div>
    </div>
  );
}

export default FormManufacturado;
