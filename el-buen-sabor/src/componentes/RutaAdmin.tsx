import { Navigate, Outlet, useOutletContext } from "react-router-dom";
import type { UsuarioSesion } from "../servicios/AuthApi";

interface SesionContext {
  usuario: UsuarioSesion | null;
  cargando: boolean;
}

function RutaAdmin() {
  const { usuario, cargando } = useOutletContext<SesionContext>();

  if (cargando) return <p className="container py-4" role="status">Consultando sesión...</p>;
  if (!usuario) return <Navigate to="/login" replace />;
  if (usuario.rol !== "admin") return <Navigate to="/" replace />;

  return <Outlet />;
}

export default RutaAdmin;
