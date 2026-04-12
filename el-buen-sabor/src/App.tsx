import Encabezado from "./componentes/Encabezado";
import PiePagina from "./componentes/PiePagina";
import { Outlet } from "react-router-dom";

function App() {
  return (
    <>
      <div className="d-flex flex-column min-vh-100">
        <Encabezado />

        <main className="flex-fill">
          <Outlet />
        </main>

        <PiePagina />
      </div>
    </>
  );
}

export default App;
