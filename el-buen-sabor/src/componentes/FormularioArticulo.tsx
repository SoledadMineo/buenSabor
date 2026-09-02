import { useState } from "react";
import FormInsumo from "./FormInsumo";
import FormManufacturado from "./FormManufacturado";

type TipoArticulo = "insumo" | "manufacturado";

function FormularioArticulo() {
  const [tipo, setTipo] = useState<TipoArticulo>("insumo");

  return (
    <div className="card shadow m-4">
      <div className="card-header bg-dark text-white">
        <h4 className="mb-0"> Alta de Producto</h4>
      </div>

      <div className="card-body">
        {/* SELECTOR DE TIPO */}
        <select
          className="form-select"
          value={tipo}
          onChange={(e) => setTipo(e.target.value as TipoArticulo)}
        >
          <option value="insumo">Insumo</option>
          <option value="manufacturado">Manufacturado</option>
        </select>

        {/* FORMULARIO SEGÚN TIPO */}
        {tipo === "insumo" && <FormInsumo />}
        {tipo === "manufacturado" && <FormManufacturado />}
      </div>
    </div>
  );
}

export default FormularioArticulo;
