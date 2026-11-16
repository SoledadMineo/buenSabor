import { Link } from "react-router-dom";
import logo from "../assets/buensabor.png";
import { useState } from "react";
import type { UsuarioSesion } from "../servicios/AuthApi";
import { useCarrito } from "../contexto/carrito";

interface Props {
  usuario: UsuarioSesion | null;
  cargando: boolean;
  saliendo: boolean;
  salir: () => Promise<void>;
}

const Encabezado = ({ usuario, cargando, saliendo, salir }: Props) => {
  const [abierto, setAbierto] = useState(false);
  const { cantidadTotal } = useCarrito();
  return (
    <nav className="navbar navbar-expand-lg bg-white shadow-sm">
      <div className="container-fluid px-4 align-items-center">
        <Link className="navbar-brand d-flex align-items-center" to="/">
          <img src={logo} alt="El Buen Sabor" height="120" />
        </Link>

        <button
          className="navbar-toggler"
          type="button"
          onClick={() => setAbierto(!abierto)}
          aria-expanded={abierto}
          aria-controls="navbarNav"
          aria-label="Abrir menu"
        >
          <span className="navbar-toggler-icon"></span>
        </button>

        <div
          className={`collapse navbar-collapse${abierto ? " show" : ""}`}
          id="navbarNav"
        >
          <ul className="navbar-nav ms-auto fs-5 gap-3">
            <li
              className="nav-item d-flex align-items-center gap-2 flex-wrap"
              style={{ overflowWrap: "anywhere" }}
            >
              {cargando ? null : usuario ? (
                <>
                  <span className="navbar-text">{usuario.username}</span>

                  <button
                    type="button"
                    className="btn btn-outline-dark"
                    disabled={saliendo}
                    onClick={() => void salir()}
                  >
                    {saliendo ? "Saliendo..." : "Salir"}
                  </button>
                </>
              ) : (
                <Link
                  className="nav-link"
                  to="/login"
                  onClick={() => setAbierto(false)}
                >
                  Ingresar
                </Link>
              )}
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/">
                Inicio
              </Link>
            </li>
            {usuario?.rol === "admin" && <li className="nav-item">
              <Link className="nav-link" to="/grilla">
                Artículos
              </Link>
            </li>}
            <li className="nav-item">
              <Link className="nav-link" to="/carrito">
                Carrito
                {cantidadTotal > 0 && <span className="badge bg-danger ms-1">{cantidadTotal}</span>}
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  );
};

export default Encabezado;
