import { useState, useEffect } from "react";
import type ArticuloInsumo from "../entidades/ArticuloInsumo";
import type ArticuloManufacturado from "../entidades/ArticuloManufacturado";
import {
  getInsumosJSONFetch,
  getManufacturadosJSONFetch,
} from "../servicios/FuncionesApi";
import sinimagen from "../assets/sinimagen.png";
import { Link } from "react-router-dom";

function Menu() {
  type ArticuloInsumoConTipo = ArticuloInsumo & {
    tipo: "insumo";
  };

  type ArticuloManufacturadoConTipo = ArticuloManufacturado & {
    tipo: "manufacturado";
  };

  type ArticuloConTipo = ArticuloInsumoConTipo | ArticuloManufacturadoConTipo;

  const [insumos, setInsumos] = useState<ArticuloInsumo[]>([]);
  const [manufacturados, setManufacturados] = useState<ArticuloManufacturado[]>(
    [],
  );
  const [articulos, setArticulos] = useState<ArticuloConTipo[]>([]);

  const getArticulos = async () => {
    const insumosData: ArticuloInsumo[] = await getInsumosJSONFetch();
    const manufacturadosData: ArticuloManufacturado[] =
      await getManufacturadosJSONFetch();

    console.log("Insumos Data:", insumosData);
    console.log("Manufacturados Data:", manufacturadosData);

    setInsumos(insumosData);
    setManufacturados(manufacturadosData);

    //    setArticulos([...insumosData, ...manufacturadosData]);

    setArticulos([
      ...insumosData.map((i) => ({ ...i, tipo: "insumo" as const })),
      ...manufacturadosData.map((m) => ({
        ...m,
        tipo: "manufacturado" as const,
      })),
    ]);
  };

  useEffect(() => {
    getArticulos();
  }, []);

  return (
    <>
      <div>
        <h2>Nuestros platos</h2>
        <div className="d-flex flex-wrap gap-4 mt-4 justify-content-center">
          {articulos.map((p) => (
            <div
              className="card"
              style={{ width: "15rem", backgroundColor: "#e5dddd" }}
              key={p.id}
            >
              <img
                src={
                  p.imagen
                    ? `http://127.0.0.1:8000/storage/${p.imagen.denominacion}`
                    : sinimagen
                }
                className="card-img-top"
                alt={p.denominacion}
              />

              <div className="card-body">
                <h5 className="card-title">{p.denominacion}</h5>
                <p className="card-text">{p.denominacion}</p>
                <div className="d-flex gap-4 justify-content-center">
                  <a
                    className="btn"
                    href="#"
                    style={{ backgroundColor: "#d53434", color: "white" }}
                  >
                    COMPRAR
                  </a>
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
          ))}
        </div>
      </div>
    </>
  );
}

export default Menu;
