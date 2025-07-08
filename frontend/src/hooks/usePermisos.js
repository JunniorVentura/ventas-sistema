import { useAuth } from '../context/AuthContext';

/**
 * Verifica si el usuario tiene un permiso específico.
 * @param {string} permiso
 * @returns {boolean}
 */
export function useTienePermiso(permiso) {
  const { permisos, loading } = useAuth();
  if (loading) return false; // Aún no están disponibles los permisos
  return permisos?.includes(permiso);
}


/**
 * Verifica si el usuario tiene TODOS los permisos indicados.
 * @param  {...string} listaPermisos
 * @returns {boolean}
 */
export function useTieneTodosLosPermisos(...listaPermisos) {
  const { permisos, loading } = useAuth();
  if (loading) return false;
  return listaPermisos && listaPermisos.every(p => permisos?.includes(p));
}

/**
 * Verifica si el usuario tiene AL MENOS UNO de los permisos indicados.
 * @param  {...string} listaPermisos
 * @returns {boolean}
 */
export function useTieneAlgunPermiso(...listaPermisos) {
  const { permisos, loading } = useAuth();
  if (loading) return false;
  return listaPermisos && listaPermisos.some(p => permisos?.includes(p));
}

/**
 * Verifica si el usuario tiene alguno de los roles indicados.
 * @param  {...string} roles
 * @returns {boolean}
 */
export function useEsRol(...roles) {
  const { usuario } = useAuth();
  const nombreRol = usuario?.rol?.nombre;
  return roles.includes(nombreRol);
}
