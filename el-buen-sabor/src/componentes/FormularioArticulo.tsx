import { useState } from "react";
import FormInsumo from "./FormInsumo";
import FormManufacturado from "./FormManufacturado";

type TipoArticulo = "insumo" | "manufacturado";

function FormularioArticulo() {
  const [tipo, setTipo] = useState<TipoArticulo>("insumo");

  return (
    <div className="container">
      <h2>Nuevo Artículo</h2>

      {/* SELECTOR DE TIPO */}
      <select
        className="form-select mb-4"
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
  );
}

export default FormularioArticulo;
