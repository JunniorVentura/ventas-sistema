import { useEffect, useRef, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';

import { useTienePermiso } from '../hooks/usePermisos';

export default function RolPermisos() {
  const puedeVer = useTienePermiso('ver_rolpermisos');
  const puedeAsignar = useTienePermiso('asignar_rolpermisos');
  const puedeEliminar = useTienePermiso('eliminar_rolpermisos');

  const [roles, setRoles] = useState([]);
  const [permisos, setPermisos] = useState([]);
  const [relaciones, setRelaciones] = useState([]);
  const [rolSeleccionado, setRolSeleccionado] = useState('');
  const [seleccionados, setSeleccionados] = useState([]);
  const [originales, setOriginales] = useState([]);

  const [busqueda, setBusqueda] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const rolSelectRef = useRef(null);

  const [showModalEliminar, setShowModalEliminar] = useState(false);
  const [relacionAEliminar, setRelacionAEliminar] = useState(null);

  const [guardando, setGuardando] = useState(false); //estadoSubmit

  const cargarDatos = async () => {
    try {
      const [resRoles, resPermisos, resRelaciones] = await Promise.all([
        api.get('/roles'),
        api.get('/permisos'),
        api.get('/rol-permisos'),
      ]);
      setRoles(resRoles.data);
      setPermisos(resPermisos.data);
      setRelaciones(resRelaciones.data);
    } catch (err) {
      toast.error('Error al cargar datos');
    }
  };

  useEffect(() => {
    if (puedeVer) {
      cargarDatos();
      setTimeout(() => rolSelectRef.current?.focus(), 300);
    }
  }, [puedeVer]);

  useEffect(() => {
    if (!rolSeleccionado) return;

    window.scrollTo({ top: 0, behavior: 'smooth' });
    api
      .get(`/rol-permisos/${rolSeleccionado}/listar`)
      .then((res) => {
        const ids = res.data.map((p) => p.id);
        setSeleccionados(ids);
        setOriginales(ids);
      })
      .catch(() => toast.error('Error al obtener permisos del rol'));
  }, [rolSeleccionado]);

  const guardarPermisos = async () => {
    if (!rolSeleccionado) return toast.warning('Selecciona un rol');
    if (!puedeAsignar) return toast.error('No tienes permiso para asignar permisos');

    const sinCambios = JSON.stringify(seleccionados.sort()) === JSON.stringify(originales.sort());
    if (sinCambios) return toast.info('No hay cambios que guardar');
    
    setGuardando(true);
    try {
      await api.post(`/rol-permisos/asignar/${rolSeleccionado}`, {
        permiso_ids: seleccionados,
      });
      toast.success('Permisos actualizados correctamente');
      setOriginales(seleccionados);
      cargarDatos();
    } catch {
      toast.error('Error al actualizar permisos');
    } finally {
      setGuardando(false);
    }
  };

  const togglePermiso = (id) => {
    setSeleccionados((prev) =>
      prev.includes(id) ? prev.filter((p) => p !== id) : [...prev, id]
    );
  };

  const abrirModalEliminar = (relacion) => {
    if (showModalEliminar) return; // Previene abrir varios modales accidentalmente
    if (!puedeEliminar) return toast.error('No tienes permiso para eliminar relaciones');
    setRelacionAEliminar(relacion);
    setShowModalEliminar(true);
  };

  const confirmarEliminarRelacion = async () => {
    if (!puedeEliminar) return toast.error('No tienes permiso para eliminar relaciones');
    try {
      await api.delete(`/rol-permisos/${relacionAEliminar.id}`);
      toast.success('Relación eliminada');
      cargarDatos();
    } catch {
      toast.error('Error al eliminar relación');
    }
    setShowModalEliminar(false);
    setRelacionAEliminar(null);
  };

  const limpiarSeleccion = () => {
    setRolSeleccionado('');
    setSeleccionados([]);
    setOriginales([]);
  };

  const relacionesFiltradas = relaciones.filter(
    (rel) =>
      rel.rol?.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
      rel.permiso?.nombre.toLowerCase().includes(busqueda.toLowerCase())
  );

  const totalPaginas = Math.ceil(relacionesFiltradas.length / porPagina);
  const relacionesPaginadas = relacionesFiltradas.slice(
    (pagina - 1) * porPagina,
    pagina * porPagina
  );

  if (!puedeVer) {
    return <div className="container mt-4"><p>No tienes permiso para ver esta sección.</p></div>;
  }

  return (
    <div className="container mt-4">
      <h4 className="mb-4 text-primary fw-bold">Asignar Permisos a Roles</h4>

      {/* Selector de rol */}
      <div className="mb-3 d-flex align-items-center gap-3">
        <div className="flex-grow-1">
          <label className="form-label fw-semibold">Rol:</label>
          <select
            ref={rolSelectRef}
            className="form-select"
            value={rolSeleccionado}
            onChange={(e) => setRolSeleccionado(e.target.value)}
          >
            <option value="">-- Selecciona un rol --</option>
            {roles.map((rol) => (
              <option key={rol.id} value={rol.id}>
                {rol.nombre}
              </option>
            ))}
          </select>
        </div>
        {rolSeleccionado && (
          <button className="btn btn-outline-secondary mt-4" onClick={limpiarSeleccion} disabled={guardando}>
            Cambiar rol
          </button>
        )}
      </div>

      {/* Permisos del rol */}
      {rolSeleccionado && (
        <div className="mb-3">
          <label className="form-label fw-semibold">Permisos:</label>
          <div className="row">
            {permisos.map((permiso) => (
              <div className="col-md-4" key={permiso.id}>
                <div className="form-check">
                  <input
                    className="form-check-input"
                    type="checkbox"
                    id={`perm-${permiso.id}`}
                    checked={seleccionados.includes(permiso.id)}
                    onChange={() => togglePermiso(permiso.id)}
                  />
                  <label className="form-check-label" htmlFor={`perm-${permiso.id}`}>
                    {permiso.nombre}
                  </label>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Botón de guardar */}
      {rolSeleccionado && puedeAsignar && (
        <div className="mb-4 d-flex justify-content-between">
          <button
            className="btn btn-success"
            onClick={guardarPermisos}
            disabled={
              JSON.stringify(seleccionados.sort()) === JSON.stringify(originales.sort())
            }
          >
            Guardar Cambios
          </button>
          <span className="text-muted align-self-center">
            {seleccionados.length} permisos seleccionados
          </span>
        </div>
      )}

      <hr className="my-4" />
      <h5 className="mb-3 text-secondary fw-semibold">Relaciones actuales</h5>
      {/* Búsqueda */}
      <div className="mb-3">
        <input
          type="text"
          placeholder="Buscar rol o permiso..."
          className="form-control"
          value={busqueda}
          onChange={(e) => {
            setBusqueda(e.target.value);
            setPagina(1);
          }}
        />
      </div>
      {/*Select para paginación*/}
      <div className="mb-3 d-flex align-items-center">
        <label className="me-2">Stock por página:</label>
        <select
          className="form-select w-auto"
          value={porPagina}
          onChange={e => {
            setPorPagina(parseInt(e.target.value));
            setPagina(1); // Reinicia a la primera página
          }}
        >
          <option value={5}>5</option>
          <option value={10}>10</option>
          <option value={15}>15</option>
          <option value={20}>20</option>
        </select>
      </div>
      {/* Tabla de relaciones */}
      <div className="table-responsive">
        <table className="table table-striped table-hover align-middle">
          <thead className="table-dark">
            <tr>
              <th>Rol</th>
              <th>Permiso</th>
              {puedeEliminar && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {relacionesPaginadas.length > 0 ? (
              relacionesPaginadas.map((rel) => (
                <tr key={rel.id}>
                  <td>{rel.rol?.nombre}</td>
                  <td>{rel.permiso?.nombre}</td>
                  {puedeEliminar && (
                    <td>
                      <button
                        className="btn btn-danger btn-sm"
                        onClick={() => abrirModalEliminar(rel)}
                        title="Eliminar relación"
                      >
                        <i className="bi bi-trash"></i> Eliminar
                      </button>
                    </td>
                  )}
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={puedeEliminar ? 3 : 2} className="text-center">
                  No hay coincidencias.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {/*Paginación*/}
      {totalPaginas > 1 && (
        <div className="d-flex justify-content-center gap-2 mt-3">
          <button className="btn btn-outline-secondary"
            disabled={pagina === 1}
            onClick={() => setPagina(pagina - 1)}>
            Anterior
          </button>
          <span className="align-self-center">Página {pagina} de {totalPaginas}</span>
          <button className="btn btn-outline-secondary"
            disabled={pagina === totalPaginas}
            onClick={() => setPagina(pagina + 1)}>
            Siguiente
          </button>
        </div>
      )}

      {/* Modal de confirmación para eliminar */}
      {showModalEliminar && (
        <div
          className="modal fade show d-block"
          tabIndex="-1"
          style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
          aria-modal="true"
          role="dialog"
        >
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">Confirmar Eliminación</h5>
                <button
                  type="button"
                  className="btn-close"
                  aria-label="Cerrar"
                  onClick={() => setShowModalEliminar(false)}
                ></button>
              </div>

              <div className="modal-body">
                <p>
                  ¿Deseas eliminar la relación entre el rol{' '}
                  <strong>{relacionAEliminar?.rol?.nombre}</strong> y el permiso{' '}
                  <strong>{relacionAEliminar?.permiso?.nombre}</strong>?
                </p>
              </div>

              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={() => setShowModalEliminar(false)}
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  className="btn btn-danger"
                  onClick={confirmarEliminarRelacion}
                >
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
