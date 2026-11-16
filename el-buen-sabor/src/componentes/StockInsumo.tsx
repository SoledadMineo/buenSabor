import { useCallback, useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import Alert from "react-bootstrap/Alert";
import { getInsumoById } from "../servicios/FuncionesApi";
import {
  ajustarStock,
  definirStock,
  getStockInsumo,
  listarSucursales,
  type StockSucursal,
  type Sucursal,
} from "../servicios/InsumosApi";

interface Fila {
  actual: string;
  minimo: string;
  maximo: string;
  ajuste: string;
}

const vacia: Fila = { actual: "0", minimo: "0", maximo: "0", ajuste: "" };

function StockInsumo() {
  const { id } = useParams();
  const insumoId = Number(id);
  const [nombre, setNombre] = useState("");
  const [sucursales, setSucursales] = useState<Sucursal[]>([]);
  const [filas, setFilas] = useState<Record<number, Fila>>({});
  const [existe, setExiste] = useState<Record<number, StockSucursal>>({});
  const [mensaje, setMensaje] = useState<{ tipo: "success" | "danger"; texto: string } | null>(null);

  const cargar = useCallback(async () => {
    try {
      const [insumo, sucs, stock] = await Promise.all([
        getInsumoById(insumoId),
        listarSucursales(),
        getStockInsumo(insumoId),
      ]);
      setNombre(insumo.denominacion);
      setSucursales(sucs);
      const porSucursal: Record<number, StockSucursal> = {};
      const nuevas: Record<number, Fila> = {};
      stock.forEach((s) => (porSucursal[s.sucursal_empresa_id] = s));
      sucs.forEach((s) => {
        const e = porSucursal[s.id];
        nuevas[s.id] = e
          ? { actual: String(e.stockActual), minimo: String(e.stockMinimo), maximo: String(e.stockMaximo), ajuste: "" }
          : { ...vacia };
      });
      setExiste(porSucursal);
      setFilas(nuevas);
    } catch (e) {
      setMensaje({ tipo: "danger", texto: (e as Error).message });
    }
  }, [insumoId]);

  useEffect(() => {
    cargar();
  }, [cargar]);

  const cambiar = (sid: number, campo: keyof Fila, valor: string) =>
    setFilas((f) => ({ ...f, [sid]: { ...f[sid], [campo]: valor } }));

  const guardar = async (sid: number) => {
    const f = filas[sid];
    try {
      await definirStock(insumoId, {
        sucursal_empresa_id: sid,
        stockActual: Number(f.actual),
        stockMinimo: Number(f.minimo),
        stockMaximo: Number(f.maximo),
      });
      setMensaje({ tipo: "success", texto: "Stock guardado." });
      cargar();
    } catch (e) {
      setMensaje({ tipo: "danger", texto: (e as Error).message });
    }
  };

  const ajustar = async (sid: number) => {
    const cantidad = Number(filas[sid].ajuste);
    if (!Number.isInteger(cantidad) || cantidad === 0) {
      setMensaje({ tipo: "danger", texto: "Ingrese una cantidad entera distinta de 0 (negativa para restar)." });
      return;
    }
    try {
      await ajustarStock(insumoId, sid, cantidad);
      setMensaje({ tipo: "success", texto: "Stock ajustado." });
      cargar();
    } catch (e) {
      setMensaje({ tipo: "danger", texto: (e as Error).message });
    }
  };

  return (
    <div className="container mt-4">
      <div className="d-flex justify-content-between align-items-center mb-3">
        <h2>Stock: {nombre}</h2>
        <Link to="/grilla" className="btn btn-dark">
          Volver
        </Link>
      </div>

      {mensaje && (
        <Alert variant={mensaje.tipo} dismissible onClose={() => setMensaje(null)}>
          {mensaje.texto}
        </Alert>
      )}

      <table className="table align-middle">
        <thead>
          <tr>
            <th>Sucursal</th>
            <th>Actual</th>
            <th>Mínimo</th>
            <th>Máximo</th>
            <th></th>
            <th>Ajuste (+/-)</th>
          </tr>
        </thead>
        <tbody>
          {sucursales.map((s) => {
            const f = filas[s.id] ?? vacia;
            const bajo = existe[s.id] && existe[s.id].stockActual < existe[s.id].stockMinimo;
            return (
              <tr key={s.id}>
                <td>
                  {s.nombre} {bajo && <span className="badge bg-danger">Stock bajo</span>}
                  {!existe[s.id] && <span className="badge bg-secondary ms-1">Sin definir</span>}
                </td>
                {(["actual", "minimo", "maximo"] as const).map((campo) => (
                  <td key={campo}>
                    <input
                      type="number"
                      min={0}
                      className="form-control form-control-sm"
                      style={{ width: 100 }}
                      value={f[campo]}
                      onChange={(e) => cambiar(s.id, campo, e.target.value)}
                    />
                  </td>
                ))}
                <td>
                  <button className="btn btn-success btn-sm" onClick={() => guardar(s.id)}>
                    Guardar
                  </button>
                </td>
                <td>
                  <div className="d-flex gap-2">
                    <input
                      type="number"
                      className="form-control form-control-sm"
                      style={{ width: 90 }}
                      placeholder="-5 / 10"
                      value={f.ajuste}
                      onChange={(e) => cambiar(s.id, "ajuste", e.target.value)}
                      disabled={!existe[s.id]}
                    />
                    <button className="btn btn-dark btn-sm" onClick={() => ajustar(s.id)} disabled={!existe[s.id]}>
                      Ajustar
                    </button>
                  </div>
                </td>
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
}

export default StockInsumo;
