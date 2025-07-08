// src/components/Sidebar.jsx
import { Link, useLocation } from 'react-router-dom';

export default function Sidebar() {
  const { pathname } = useLocation();

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
        { to: '/usuarios', icon: 'bi-person-lines-fill', label: 'Usuarios' },
        { to: '/roles', icon: 'bi-shield-lock', label: 'Roles' },
        { to: '/permisos', icon: 'bi-lock-fill', label: 'Permisos' },
        { to: '/rol-permisos', icon: 'bi-sliders', label: 'Rol-Permisos' },
      ],
    },
    {
      title: 'Gestión',
      links: [
        { to: '/clientes', icon: 'bi-people-fill', label: 'Clientes' },
        { to: '/categorias', icon: 'bi-tags', label: 'Categorías' },
        { to: '/productos', icon: 'bi-box-seam', label: 'Productos' },
        { to: '/stock', icon: 'bi-boxes', label: 'Stock' },
        { to: '/ventas', icon: 'bi-cash-stack', label: 'Ventas' },
        { to: '/pagos', icon: 'bi-credit-card', label: 'Pagos' },
      ],
    },
    {
      title: 'Documentos',
      links: [
        { to: '/pedidos', icon: 'bi-basket', label: 'Pedidos' },
        { to: '/boletas', icon: 'bi-receipt', label: 'Boletas' },
        { to: '/facturas', icon: 'bi-file-earmark-text', label: 'Facturas' },
        { to: '/reportes', icon: 'bi-bar-chart-line', label: 'Reportes' },
      ],
    },
  ];

  return (
    <div className="bg-light border-end vh-100 p-3" style={{ width: '250px' }}>
      <h5 className="text-primary mb-4">Menú Principal</h5>
      {sections.map((section, idx) => (
        <div key={idx} className="mb-3">
          <small className="text-muted text-uppercase">{section.title}</small>
          <ul className="nav flex-column mt-1">
            {section.links.map(({ to, icon, label }) => (
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
      ))}
    </div>
  );
}
