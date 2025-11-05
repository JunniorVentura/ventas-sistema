// src/components/Sidebar.jsx
import { Link, useLocation } from 'react-router-dom';
import { useTienePermiso } from '../hooks/usePermisos';

export default function Sidebar() {
  const { pathname } = useLocation();

  // Permiso checker
  const tienePermiso = useTienePermiso;

  const sections = [
    {
      title: 'General',
      links: [
        { to: '/dashboard', icon: 'bi-speedometer2', label: 'Dashboard' },
      ],
    },
    {
      title: 'Usuarios y Seguridad',
      links: [
        { to: '/usuarios', icon: 'bi-person-lines-fill', label: 'Usuarios', permiso: 'ver_usuarios' },
        { to: '/roles', icon: 'bi-shield-lock', label: 'Roles', permiso: 'ver_roles' },
        { to: '/permisos', icon: 'bi-lock-fill', label: 'Permisos', permiso: 'ver_permisos' },
        { to: '/rol-permisos', icon: 'bi-sliders', label: 'Rol-Permisos', permiso: 'ver_rolpermisos' },
      ],
    },
    {
      title: 'Gestión',
      links: [
        { to: '/clientes', icon: 'bi-people-fill', label: 'Clientes', permiso: 'ver_clientes' },
        { to: '/categorias', icon: 'bi-tags', label: 'Categorías', permiso: 'ver_categorias' },
        { to: '/productos', icon: 'bi-box-seam', label: 'Productos', permiso: 'ver_productos' },
        { to: '/stock', icon: 'bi-boxes', label: 'Stock', permiso: 'ver_stock' },
        { to: '/ventas', icon: 'bi-cash-stack', label: 'Ventas', permiso: 'ver_pedidos' },
        { to: '/pagos', icon: 'bi-credit-card', label: 'Pagos', permiso: 'ver_pagos' },
      ],
    },
    {
      title: 'Documentos',
      links: [
        { to: '/pedidos', icon: 'bi-basket', label: 'Pedidos', permiso: 'ver_pedidos' },
        { to: '/boletas', icon: 'bi-receipt', label: 'Boletas', permiso: 'ver_boletas' },
        { to: '/facturas', icon: 'bi-file-earmark-text', label: 'Facturas', permiso: 'ver_facturas' },
        { to: '/reportes', icon: 'bi-bar-chart-line', label: 'Reportes', permiso: 'exportar_reportes' },
      ],
    },
  ];

  return (
    <div className="bg-light border-end vh-100 p-3" style={{ width: '250px' }}>
      <h5 className="text-primary mb-4">Menú Principal</h5>
      {sections.map((section, idx) => {
        const visibleLinks = section.links.filter(link =>
          !link.permiso || tienePermiso(link.permiso)
        );

        if (visibleLinks.length === 0) return null; // Oculta la sección si no hay enlaces visibles

        return (
          <div key={idx} className="mb-3">
            <small className="text-muted text-uppercase">{section.title}</small>
            <ul className="nav flex-column mt-1">
              {visibleLinks.map(({ to, icon, label }) => (
                <li className="nav-item" key={to}>
                  <Link
                    className={`nav-link d-flex align-items-center rounded py-2 px-2 ${
                      pathname === to ? 'bg-primary text-white fw-bold' : 'text-dark'
                    }`}
                    to={to}
                  >
                    <i className={`bi ${icon} me-2`}></i>
                    {label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        );
      })}
    </div>
  );
}
