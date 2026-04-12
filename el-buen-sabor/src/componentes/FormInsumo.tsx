import { useEffect, useState } from "react";
import {
  getUnidadesMedida,
  getCategoriasArticulo,
  saveInsumo,
} from "../servicios/FuncionesApi";

function FormInsumo() {
  const [articulo, setArticulo] = useState({
    denominacion: "",
    precioCompra: 0,
    precioVenta: 0,
    esParaElaborar: false,
    unidad_medida_id: 0,
    categoria_articulo_id: 0,
  });
  const dto = {
    denominacion: articulo.denominacion.trim(),
    precioCompra: articulo.precioCompra,
    precioVenta: articulo.precioVenta,
    esParaElaborar: articulo.esParaElaborar ? 1 : 0,
    unidad_medida_id: articulo.unidad_medida_id,
    categoria_articulo_id: articulo.categoria_articulo_id,
  };

  const [unidades, setUnidades] = useState<any[]>([]);
  const [categorias, setCategorias] = useState<any[]>([]);
  const [mensaje, setMensaje] = useState<string>("");
  const [imagen, setImagen] = useState<File | null>(null);

  const guardar = async () => {
    const error = validar();
    if (error) {
      setMensaje(error);
      return;
    }

    const formData = new FormData();
    formData.append("denominacion", articulo.denominacion);
    formData.append("precioCompra", articulo.precioCompra.toString());
    formData.append("precioVenta", articulo.precioVenta.toString());
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
      setMensaje("Insumo guardado correctamente");
    } catch {
      setMensaje("Error al guardar insumo");
    }
  };

  const validar = () => {
    if (!articulo.denominacion.trim()) {
      return "La denominación es obligatoria";
    }

    if (articulo.precioCompra <= 0) {
      return "El precio de compra debe ser mayor a 0";
    }

    if (articulo.precioVenta <= 0) {
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
      <input
        className="form-control mb-3"
        placeholder="Denominación"
        value={articulo.denominacion}
        onChange={(e) =>
          setArticulo({
            ...articulo,
            denominacion: e.target.value,
          })
        }
      />

      <input
        type="number"
        className="form-control mb-3"
        placeholder="Precio compra"
        value={articulo.precioCompra}
        onChange={(e) =>
          setArticulo({
            ...articulo,
            precioCompra: Number(e.target.value),
          })
        }
      />

      <input
        type="number"
        className="form-control mb-3"
        placeholder="Precio venta"
        value={articulo.precioVenta}
        onChange={(e) =>
          setArticulo({
            ...articulo,
            precioVenta: Number(e.target.value),
          })
        }
      />

      <div className="form-check mb-3">
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
        <label className="form-check-label" htmlFor="esParaElaborar">
          Es para elaborar
        </label>
      </div>

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
        <option value={0}>Seleccione categoría</option>

        {categorias.map((c) => (
          <option key={c.id} value={c.id}>
            {c.denominacion}
          </option>
        ))}
      </select>

      <input
        type="file"
        className="form-control mb-3"
        accept="image/*"
        onChange={(e) => {
          if (e.target.files && e.target.files[0]) {
            setImagen(e.target.files[0]);
          }
        }}
      />

      <button className="btn btn-success" onClick={guardar}>
        Guardar
      </button>

      {mensaje && (
        <div
          className={`alert mt-3 ${
            mensaje.includes("guardado") ? "alert-success" : "alert-danger"
          }`}
        >
          {mensaje}
        </div>
      )}
    </>
  );
}

export default FormInsumo;
