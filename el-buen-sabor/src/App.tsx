import Encabezado from "./componentes/Encabezado";
import PiePagina from "./componentes/PiePagina";
import { Outlet, useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";
import { consultarSesion, cerrarSesion } from "./servicios/AuthApi";
import type { UsuarioSesion } from "./servicios/AuthApi";

function App() {
  const [usuario, setUsuario] = useState<UsuarioSesion | null>(null);
  const [cargando, setCargando] = useState(true);
  const [saliendo, setSaliendo] = useState(false);
  const [error, setError] = useState("");
  const navigate = useNavigate();

  useEffect(() => {
    let activo = true;
    consultarSesion().then((data) => { if (activo) setUsuario(data); })
      .catch(() => { if (activo) setError("No se pudo consultar la sesion. Compruebe la conexion."); })
      .finally(() => { if (activo) setCargando(false); });
    return () => { activo = false; };
  }, []);

  const salir = async () => {
    if (saliendo) return;
    setSaliendo(true);
    setError("");
    try {
      await cerrarSesion();
      setUsuario(null);
      navigate("/login");
    } catch {
      setError("No se pudo cerrar la sesion. Intente nuevamente.");
    } finally {
      setSaliendo(false);
    }
  };
  return (
    <>
      <div className="d-flex flex-column min-vh-100">
        <Encabezado usuario={usuario} cargando={cargando} saliendo={saliendo} salir={salir} />

        <main className="flex-fill">
          {error && <div className="container pt-3"><p className="alert alert-danger" role="alert">{error}</p></div>}
          <Outlet context={{ usuario, setUsuario: (value: UsuarioSesion | null) => { setUsuario(value); setError(""); }, cargando }} />
        </main>

        <PiePagina />
      </div>
    </>
  );
}

export default App;
