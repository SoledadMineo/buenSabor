import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import App from "./App.tsx";
import Menu from "./componentes/Menu.tsx";
import Detalle from "./componentes/Detalle.tsx";
import GrillaArticulo from "./componentes/GrillaArticulo.tsx";
import FormularioArticulo from "./componentes/FormularioArticulo.tsx";
import "bootstrap/dist/css/bootstrap.min.css";

ReactDOM.createRoot(document.getElementById("root")!).render(
  <React.StrictMode>
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<App />}>
          <Route index element={<Menu />} />
          <Route path="detalle/:tipo/:id" element={<Detalle />} />
          <Route path="/grilla" element={<GrillaArticulo />} />
          <Route
            path="/admin/articulos/nuevo"
            element={<FormularioArticulo />}
          />
        </Route>
      </Routes>
    </BrowserRouter>
  </React.StrictMode>,
);
