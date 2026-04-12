import { Link, useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { getInsumoById, getManufacturadoById } from "../servicios/FuncionesApi";
import type ArticuloInsumo from "../entidades/ArticuloInsumo";
import type ArticuloManufacturado from "../entidades/ArticuloManufacturado";
import sinimagen from "../assets/sinimagen.png";

type Producto = ArticuloInsumo | ArticuloManufacturado;

function DetalleProducto() {
  const { id, tipo } = useParams();
  const [producto, setProducto] = useState<Producto | null>(null);
  console.log("params:", tipo, id);

  useEffect(() => {
    if (!id || !tipo) return;

    if (tipo === "insumo") {
      getInsumoById(Number(id)).then(setProducto);
    } else if (tipo === "manufacturado") {
      getManufacturadoById(Number(id)).then(setProducto);
    }
  }, [id, tipo]);

  if (!producto) return <p>Cargando...</p>;

  return (
    <div>
      <h2>{producto.denominacion}</h2>
      <p>Precio: ${producto.precioVenta}</p>
      <div
        className="card"
        style={{ width: "18rem", backgroundColor: "#e5dddd" }}
      >
        <img src={sinimagen} className="card-img-top" alt="..." />
        <div className="card-body">
          <h5 className="card-title">{producto.denominacion}</h5>
          <p className="card-text">{producto.denominacion}</p>
          <div className="d-flex gap-4 justify-content-center">
            <a
              className="btn btn-primary"
              href="#"
              style={{ backgroundColor: "#d53434", color: "white" }}
            >
              COMPRAR
            </a>
            <Link
              to="/"
              className="btn btn-primary"
              style={{ backgroundColor: "#747474", color: "white" }}
            >
              VOLVER
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}

export default DetalleProducto;
