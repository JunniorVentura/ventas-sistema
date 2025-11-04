// src/routes/Routes.jsx
import { Routes, Route, Navigate } from 'react-router-dom';
import PrivateRoute from './PrivateRoute';

import Login from '../pages/Login';
import Layout from '../components/Layout';
import Dashboard from '../pages/Dashboard';
import Usuarios from '../pages/Usuarios';
import Roles from '../pages/Roles';
import Permisos from '../pages/Permisos';
import RolPermisos from '../pages/RolPermisos';
import Clientes from '../pages/Clientes';
import Categorias from '../pages/Categorias';
import Productos from '../pages/Productos';
import Stock from '../pages/Stock';
import Pagos from '../pages/Pagos';
import Ventas from '../pages/Ventas';
import Pedidos from '../pages/Pedidos';
import Boletas from '../pages/Boletas';
import Facturas from '../pages/Facturas';
import Reportes from '../pages/Reportes';
import Perfil from '../pages/Perfil';
import MiPerfil from '../pages/MiPerfil';
import CambiarPassword from '../pages/CambiarPassword';

export default function RouterApp() {
  return (
      <Routes>
        {/* RUTA PÚBLICA */}
        <Route path="/" element={<Login />} />

        {/* RUTAS PROTEGIDAS <Route index element={<Navigate to="/dashboard" replace />} />*/}
        <Route
          path="/"
          element={
            <PrivateRoute>
              <Layout />
            </PrivateRoute>
          }
        >
          
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="categorias" element={<Categorias />} />
          <Route path="clientes" element={<Clientes />} />
          <Route path="productos" element={<Productos />} />
          <Route path="stock" element={<Stock />} />
          <Route path="ventas" element={<Ventas />} />
          <Route path="pagos" element={<Pagos />} />
          <Route path="reportes" element={<Reportes />} />
          <Route path="perfil" element={<Perfil />} />
          <Route path="mi-perfil" element={<MiPerfil />} />
          <Route path="usuarios" element={<Usuarios />} />
          <Route path="roles" element={<Roles />} />
          <Route path="permisos" element={<Permisos />} />
          <Route path="rol-permisos" element={<RolPermisos />} />
          <Route path="pedidos" element={<Pedidos />} />
          <Route path="boletas" element={<Boletas />} />
          <Route path="facturas" element={<Facturas />} />
          <Route path="cambiar-password" element={<CambiarPassword />} />
        </Route>
      </Routes>
  );
}
