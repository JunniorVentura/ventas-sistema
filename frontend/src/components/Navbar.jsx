import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import { useEffect, useRef, useState } from 'react';

export default function Navbar() {
  const { usuario, logout } = useAuth();
  const navigate = useNavigate();
  const [mostrarDropdown, setMostrarDropdown] = useState(false);
  const dropdownRef = useRef(null);

  const toggleDropdown = () => {
    setMostrarDropdown(prev => !prev);
  };

  const cerrarSesion = () => {
    setMostrarDropdown(false);
    logout();
  };

  const irAMiPerfil = () => {
    setMostrarDropdown(false);
    navigate('/perfil');
  };

  const irCambiarPassword = () => {
    setMostrarDropdown(false);
    navigate('/cambiar-password');
  };

  // Cerrar dropdown al hacer clic fuera
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setMostrarDropdown(false);
      }
    };

    if (mostrarDropdown) {
      document.addEventListener('mousedown', handleClickOutside);
    } else {
      document.removeEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [mostrarDropdown]);

  return (
    <nav className="navbar navbar-dark bg-dark px-4 shadow-sm">
      <div className="container-fluid d-flex justify-content-between align-items-center">
        <span className="navbar-brand mb-0 h1">
          <i className="bi bi-shop me-2" />
          Sistema de Ventas
        </span>

        {usuario && (
          <div className="dropdown text-end" ref={dropdownRef}>
            <button
              onClick={toggleDropdown}
              className="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
              aria-expanded={mostrarDropdown}
              aria-haspopup="true"
              type="button"
            >
              <i className="bi bi-person-circle"></i>
              <span className="d-none d-sm-inline">{usuario.nombre}</span>
            </button>

            {mostrarDropdown && (
              <ul className="dropdown-menu dropdown-menu-end show mt-2">
                <li>
                  <button className="dropdown-item" onClick={irAMiPerfil}>
                    <i className="bi bi-person-lines-fill me-2" />
                    Mi perfil
                  </button>
                </li>
                <li>
                  <button className="dropdown-item" onClick={irCambiarPassword}>
                    <i className="bi bi-key-fill me-2" />
                    Cambiar contraseña
                  </button>
                </li>
                <li><hr className="dropdown-divider" /></li>
                <li>
                  <button className="dropdown-item text-danger" onClick={cerrarSesion}>
                    <i className="bi bi-box-arrow-right me-2" />
                    Cerrar sesión
                  </button>
                </li>
              </ul>
            )}
          </div>
        )}
      </div>
    </nav>
  );
}
