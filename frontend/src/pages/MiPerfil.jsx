// src/pages/MiPerfil.jsx
import { useAuth } from '../context/AuthContext';

export default function MiPerfil() {
  const { usuario, permisos } = useAuth();

  return (
    <div className="container mt-4">
      <h3>Mi Perfil</h3>
      <p><strong>Nombre:</strong> {usuario?.nombre}</p>
      <p><strong>Correo:</strong> {usuario?.email}</p>
      <p><strong>Rol:</strong> {usuario?.rol?.nombre}</p>

      <h5>Permisos:</h5>
      <ul>
        {permisos?.map((permiso, i) => (
          <li key={i}>{permiso}</li>
        ))}
      </ul>
    </div>
  );
}
