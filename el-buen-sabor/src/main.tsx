import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import App from "./App.tsx";
import Login from "./componentes/Login.tsx";
import Menu from "./componentes/Menu.tsx";
import Detalle from "./componentes/Detalle.tsx";
import GrillaArticulo from "./componentes/GrillaArticulo.tsx";
import FormularioArticulo from "./componentes/FormularioArticulo.tsx";
import FormInsumo from "./componentes/FormInsumo.tsx";
import FormManufacturado from "./componentes/FormManufacturado.tsx";
import StockInsumo from "./componentes/StockInsumo.tsx";
import RutaAdmin from "./componentes/RutaAdmin.tsx";
import "bootstrap/dist/css/bootstrap.min.css";

ReactDOM.createRoot(document.getElementById("root")!).render(
  <React.StrictMode>
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<App />}>
          <Route index element={<Menu />} />
          <Route path="login" element={<Login />} />
          <Route path="detalle/:tipo/:id" element={<Detalle />} />
          <Route element={<RutaAdmin />}>
            <Route path="grilla" element={<GrillaArticulo />} />
            <Route path="admin/articulos/nuevo" element={<FormularioArticulo />} />
            <Route path="admin/insumos/:id/editar" element={<FormInsumo />} />
            <Route path="admin/manufacturados/:id/editar" element={<FormManufacturado />} />
            <Route path="admin/insumos/:id/stock" element={<StockInsumo />} />
          </Route>
        </Route>
      </Routes>
    </BrowserRouter>
  </React.StrictMode>,
);
