import { useState } from "react";
import { Navigate, useNavigate, useOutletContext } from "react-router-dom";
import { iniciarSesion } from "../servicios/AuthApi";
import type { UsuarioSesion } from "../servicios/AuthApi";

interface SesionContext {
  usuario: UsuarioSesion | null;
  setUsuario: (usuario: UsuarioSesion | null) => void;
  cargando: boolean;
}

function Login() {
  const navigate = useNavigate();
  const sesion = useOutletContext<SesionContext>();
  const [usuario, setUsuario] = useState({ usuario: "", clave: "" });
  const [txtValidacion, setTxtValidacion] = useState("");
  const [enviando, setEnviando] = useState(false);

  const login = async () => {
    if (enviando) return;
    if (!usuario.usuario.trim()) {
      setTxtValidacion("Ingrese el nombre de usuario");
      return;
    }
    if (!usuario.clave) {
      setTxtValidacion("Ingrese la clave");
      return;
    }
    setTxtValidacion("");
    setEnviando(true);
    try {
      const usuarioLogueado = await iniciarSesion(usuario.usuario.trim(), usuario.clave);
      sesion.setUsuario(usuarioLogueado);
      navigate("/", { replace: true });
    } catch (error) {
      setTxtValidacion(error instanceof Error ? error.message : "No se pudo ingresar.");
    } finally {
      setEnviando(false);
    }
  };

  if (sesion.cargando) return <p className="container py-4" role="status">Consultando sesion...</p>;
  if (sesion.usuario) return <Navigate to="/" replace />;

  return (
    <section className="container py-5" style={{ maxWidth: 460 }}>
      <h1 className="h3 mb-4">Ingresar</h1>
      <form onSubmit={(event) => { event.preventDefault(); void login(); }}>
        <div className="mb-3">
          <label htmlFor="txtUsuario" className="form-label">Usuario</label>
          <input id="txtUsuario" className="form-control" type="text" autoComplete="username"
            maxLength={255} disabled={enviando} value={usuario.usuario}
            onChange={(e) => setUsuario({ ...usuario, usuario: e.target.value })} />
        </div>
        <div className="mb-3">
          <label htmlFor="txtClave" className="form-label">Clave</label>
          <input id="txtClave" className="form-control" type="password" autoComplete="current-password"
            maxLength={255} disabled={enviando} value={usuario.clave}
            onChange={(e) => setUsuario({ ...usuario, clave: e.target.value })} />
        </div>
        {txtValidacion && <p className="text-danger" role="alert">{txtValidacion}</p>}
        <button className="btn btn-dark w-100" type="submit" disabled={enviando}>
          {enviando ? "Ingresando..." : "Ingresar"}
        </button>
      </form>
    </section>
  );
}

export default Login;
