import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import {
  getUnidadesMedida,
  getCategoriasArticulo,
  saveInsumo,
} from "../servicios/FuncionesApi";
import Alert from "react-bootstrap/Alert";

interface OpcionSeleccionable {
  id: number;
  denominacion: string;
}

function FormInsumo() {
  const [articulo, setArticulo] = useState({
    denominacion: "",
    precioCompra: "",
    precioVenta: "",
    esParaElaborar: false,
    unidad_medida_id: 0,
    categoria_articulo_id: 0,
  });
  // const dto = {
  //   denominacion: articulo.denominacion.trim(),
  //   precioCompra: articulo.precioCompra,
  //   precioVenta: articulo.precioVenta,
  //   esParaElaborar: articulo.esParaElaborar ? 1 : 0,
  //   unidad_medida_id: articulo.unidad_medida_id,
  //   categoria_articulo_id: articulo.categoria_articulo_id,
  // };

  const [unidades, setUnidades] = useState<OpcionSeleccionable[]>([]);
  const [categorias, setCategorias] = useState<OpcionSeleccionable[]>([]);
  const [mensaje, setMensaje] = useState("");
  const [tipoMensaje, setTipoMensaje] = useState<
    "success" | "danger" | "warning" | "info"
  >("success");
  const [imagen, setImagen] = useState<File | null>(null);
  const navigate = useNavigate();
  const previewImagen = imagen ? URL.createObjectURL(imagen) : null;
  const guardar = async () => {
    const error = validar();

    if (error) {
      setTipoMensaje("warning");
      setMensaje(error);
      return;
    }
    const formData = new FormData();
    formData.append("denominacion", articulo.denominacion);
    formData.append("precioCompra", Number(articulo.precioCompra).toString());
    formData.append("precioVenta", Number(articulo.precioVenta).toString());
    formData.append("esParaElaborar", articulo.esParaElaborar ? "1" : "0");
    formData.append("unidad_medida_id", articulo.unidad_medida_id.toString());
    formData.append(
      "categoria_articulo_id",
      articulo.categoria_articulo_id.toString(),
    );

    if (imagen) {
      formData.append("imagen", imagen);
    }

    try {
      await saveInsumo(formData);

      setTipoMensaje("success");
      setMensaje("✅ Producto guardado correctamente");

      setTimeout(() => {
        navigate("/grilla");
      }, 1500);
    } catch {
      setTipoMensaje("danger");
      setMensaje("❌ Error al guardar el producto");
    }
  };

  const validar = () => {
    if (!articulo.denominacion.trim()) {
      return "La denominación es obligatoria";
    }

    if (Number(articulo.precioCompra) <= 0) {
      return "El precio de compra debe ser mayor a 0";
    }

    if (Number(articulo.precioVenta) <= 0) {
      return "El precio de venta debe ser mayor a 0";
    }

    if (articulo.unidad_medida_id === 0) {
      return "Seleccione una unidad de medida";
    }

    if (articulo.categoria_articulo_id === 0) {
      return "Seleccione una categoría";
    }

    return "";
  };

  useEffect(() => {
    const cargarCombos = async () => {
      const unidadesData = await getUnidadesMedida();
      setUnidades(unidadesData);

      const categoriasData = await getCategoriasArticulo();
      setCategorias(categoriasData);
    };

    cargarCombos();
  }, []);

  return (
    <>
      <div className="container mt-4">
        {mensaje && <Alert variant={tipoMensaje}>{mensaje}</Alert>}
        <div className="card shadow">
          <div className="card-body">
            <label className="form-label fw-semibold">
              Denominación <span className="text-danger">*</span>
            </label>
            <input
              className="form-control mb-3"
              value={articulo.denominacion}
              onChange={(e) =>
                setArticulo({
                  ...articulo,
                  denominacion: e.target.value,
                })
              }
            />

            <div className="row">
              <div className="col-md-6">
                <label className="form-label fw-semibold">
                  Precio Compra <span className="text-danger">*</span>
                </label>
                <div className="input-group">
                  <span className="input-group-text">$</span>
                  <input
                    type="text"
                    inputMode="numeric"
                    className="input-group"
                    value={articulo.precioCompra}
                    onChange={(e) =>
                      setArticulo({
                        ...articulo,
                        precioCompra: e.target.value,
                      })
                    }
                  />
                </div>
              </div>

              <div className="col-md-6">
                <label className="form-label fw-semibold">Precio Venta</label>
                <div className="input-group">
                  <span className="input-group-text">$</span>
                  <input
                    type="text"
                    inputMode="numeric"
                    className="input-group"
                    value={articulo.precioVenta}
                    onChange={(e) =>
                      setArticulo({
                        ...articulo,
                        precioVenta: e.target.value,
                      })
                    }
                  />
                </div>
              </div>
            </div>

            <div className="row">
              <div className="col-md-6">
                <label className="form-label fw-semibold">
                  Categoría <span className="text-danger">*</span>
                </label>
                <select
                  className="form-select mb-3"
                  value={articulo.categoria_articulo_id}
                  onChange={(e) =>
                    setArticulo({
                      ...articulo,
                      categoria_articulo_id: Number(e.target.value),
                    })
                  }
                >
                  <option value={0}>Seleccione una categoría</option>

                  {categorias.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.denominacion}
                    </option>
                  ))}
                </select>
              </div>
              <div className="col-md-6">
                <label className="form-label fw-semibold">
                  Unidad de Medida <span className="text-danger">*</span>
                </label>
                <select
                  className="form-select mb-3"
                  value={articulo.unidad_medida_id}
                  onChange={(e) =>
                    setArticulo({
                      ...articulo,
                      unidad_medida_id: Number(e.target.value),
                    })
                  }
                >
                  <option value={0}>Seleccione unidad de medida</option>

                  {unidades.map((u) => (
                    <option key={u.id} value={u.id}>
                      {u.denominacion}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div className="border rounded p-3 bg-light">
              <div className="form-check">
                <input
                  className="form-check-input"
                  type="checkbox"
                  id="esParaElaborar"
                  checked={articulo.esParaElaborar}
                  onChange={(e) =>
                    setArticulo({
                      ...articulo,
                      esParaElaborar: e.target.checked,
                    })
                  }
                />
                <label
                  className="form-check-label fw-semibold"
                  htmlFor="esParaElaborar"
                >
                  Es para elaborar <span className="text-danger">*</span>
                </label>
              </div>
            </div>

            <div className="mb-3">
              <label className="form-label fw-semibold">
                Imagen del producto
              </label>
              <input
                type="file"
                className="form-control"
                accept="image/*"
                onChange={(e) => {
                  if (e.target.files && e.target.files[0]) {
                    setImagen(e.target.files[0]);
                  }
                }}
              />

              {imagen && (
                <div className="mt-3 text-center">
                  <img
                    src={previewImagen!}
                    alt="Vista previa"
                    className="img-thumbnail shadow-sm"
                    style={{
                      maxWidth: "220px",
                      maxHeight: "220px",
                      objectFit: "cover",
                      borderRadius: 12,
                    }}
                  />

                  <div className="mt-2 text-muted small">{imagen.name}</div>
                </div>
              )}
            </div>
          </div>
          <div className="d-flex justify-content-end gap-2 m-2">
            <button
              className="btn btn-dark"
              onClick={() => navigate("/grilla")}
            >
              Cancelar
            </button>

            <button className="btn btn-success" onClick={guardar}>
              Guardar
            </button>
          </div>
        </div>
      </div>
    </>
  );
}

export default FormInsumo;
