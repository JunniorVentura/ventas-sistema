import { useEffect, useRef, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import { useTienePermiso } from '../hooks/usePermisos';

export default function Permisos() {
  const puedeVer = useTienePermiso('ver_permisos');
  const puedeCrear = useTienePermiso('crear_permisos');
  const puedeEditar = useTienePermiso('editar_permisos');
  const puedeEliminar = useTienePermiso('eliminar_permisos');

  const [permisos, setPermisos] = useState([]);
  const [nombre, setNombre] = useState('');
  const [descripcion, setDescripcion] = useState('');
  const [editando, setEditando] = useState(null);

  const [busqueda, setBusqueda] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const inputNombreRef = useRef(null);

  const [showModalEditar, setShowModalEditar] = useState(false);
  const [showModalEliminar, setShowModalEliminar] = useState(false);
  const [permisoSeleccionado, setPermisoSeleccionado] = useState(null);

  const [guardando, setGuardando] = useState(false); //estadoSubmit

  useEffect(() => {
    if (puedeVer) {
      obtenerPermisos();
    }
  }, [puedeVer]);

  const obtenerPermisos = async () => {
    try {
      const res = await api.get('/permisos');
      setPermisos(res.data);
    } catch {
      toast.error('Error al obtener los permisos');
    }
  };

  const limpiarFormulario = () => {
    setNombre('');
    setDescripcion('');
    setEditando(null);
    setTimeout(() => inputNombreRef.current?.focus(), 200);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!nombre.trim()) {
      return toast.warning('El nombre es obligatorio');
    }

    if (!puedeCrear && !puedeEditar) {
      return toast.error('No tienes permiso para esta acción');
    }

    setGuardando(true);
    try {
      if (editando) {
        if (!puedeEditar) return toast.error('No tienes permiso para editar');
        await api.put(`/permisos/${editando}`, { nombre, descripcion });
        toast.success('Permiso actualizado');
      } else {
        if (!puedeCrear) return toast.error('No tienes permiso para crear');
        await api.post('/permisos', { nombre, descripcion });
        toast.success('Permiso creado');
      }
      limpiarFormulario();
      obtenerPermisos();
    } catch {
      toast.error('Error al guardar el permiso');
    } finally {
      setGuardando(false);
    }
  };

  const abrirModalEditar = (permiso) => {
    if (showModalEditar) return; // Previene abrir varios modales accidentalmente
    setPermisoSeleccionado(permiso);
    setShowModalEditar(true);
  };

  const confirmarEditar = () => {
    setEditando(permisoSeleccionado.id);
    setNombre(permisoSeleccionado.nombre);
    setDescripcion(permisoSeleccionado.descripcion || '');
    setShowModalEditar(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => inputNombreRef.current?.focus(), 300);
  };

  const abrirModalEliminar = (permiso) => {
    setPermisoSeleccionado(permiso);
    setShowModalEliminar(true);
  };

  const confirmarEliminar = async () => { 
    if (showModalEliminar) return; // Previene abrir varios modales accidentalmente
    if (!puedeEliminar) return toast.error('No tienes permiso para eliminar');
    try {
      await api.delete(`/permisos/${permisoSeleccionado.id}`);
      toast.success('Permiso desactivado');
      obtenerPermisos();
    } catch {
      toast.error('Error al desactivar el permiso');
    }
    setShowModalEliminar(false);
    setPermisoSeleccionado(null);
  };

  if (!puedeVer) {
    return <div className="container mt-4"><p>No tienes permiso para ver permisos.</p></div>;
  }

  const permisosFiltrados = permisos.filter((permiso) =>
    permiso.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
    (permiso.descripcion || '').toLowerCase().includes(busqueda.toLowerCase())
  );

  const totalPaginas = Math.ceil(permisosFiltrados.length / porPagina);
  const permisosPaginados = permisosFiltrados.slice(
    (pagina - 1) * porPagina,
    pagina * porPagina
  );

  return (
    <div className="container mt-4">
      <h4 className="mb-3">{editando ? 'Editar Permiso' : 'Permisos'}</h4>

      {(puedeCrear || puedeEditar) && (
        <form onSubmit={handleSubmit} className="mb-4">
          <div className="row g-3 align-items-center">
            <div className="col-md-4">
              <input
                ref={inputNombreRef}
                type="text"
                className="form-control"
                placeholder="Nombre del permiso"
                value={nombre}
                onChange={(e) => setNombre(e.target.value)}
                required
              />
            </div>

            <div className="col-md-4">
              <input
                type="text"
                className="form-control"
                placeholder="Descripción (opcional)"
                value={descripcion}
                onChange={(e) => setDescripcion(e.target.value)}
              />
            </div>

            <div className="col-md-4 d-flex gap-2">
              <button className="btn btn-primary" type="submit" disabled={guardando}>
                {editando ? 'Actualizar' : 'Registrar'}
              </button>
              {editando && (
                <button
                  className="btn btn-secondary"
                  type="button"
                  onClick={limpiarFormulario}
                >
                  Cancelar
                </button>
              )}
            </div>
          </div>
        </form>
      )}
      {/* Búsqueda */}
      <div className="mb-3">
        <input
          type="text"
          placeholder="Buscar permiso..."
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
        <label className="me-2">Permisos por página:</label>
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
      <div className="table-responsive">
        <table className="table table-bordered table-hover align-middle">
          <thead className="table-dark">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Descripción</th>
              {(puedeEditar || puedeEliminar) && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {permisosPaginados.map((permiso) => (
              <tr key={permiso.id}>
                <td>{permiso.id}</td>
                <td>{permiso.nombre}</td>
                <td>{permiso.descripcion || '-'}</td>
                {(puedeEditar || puedeEliminar) && (
                  <td>
                    {puedeEditar && (
                      <button
                        className="btn btn-sm btn-warning me-2"
                        onClick={() => abrirModalEditar(permiso)}
                      >
                        Editar
                      </button>
                    )}
                    {puedeEliminar && (
                      <button
                        className="btn btn-sm btn-danger"
                        onClick={() => abrirModalEliminar(permiso)}
                      >
                        Eliminar
                      </button>
                    )}
                  </td>
                )}
              </tr>
            ))}
            {permisosPaginados.length === 0 && (
              <tr>
                <td colSpan="4" className="text-center">
                  No hay resultados
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

      {showModalEditar && (
        <div className="modal fade show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} aria-modal="true" role="dialog">
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">Confirmar Edición</h5>
                <button
                  type="button"
                  className="btn-close"
                  aria-label="Cerrar"
                  onClick={() => setShowModalEditar(false)}
                ></button>
              </div>
              <div className="modal-body">
                <p>¿Deseas editar el permiso <strong>{permisoSeleccionado?.nombre}</strong>?</p>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-secondary" onClick={() => setShowModalEditar(false)}>Cancelar</button>
                <button type="button" className="btn btn-primary" onClick={confirmarEditar}>Confirmar</button>
              </div>
            </div>
          </div>
        </div>
      )}

      {showModalEliminar && (
        <div className="modal fade show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} aria-modal="true" role="dialog">
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
                <p>¿Deseas eliminar el permiso <strong>{permisoSeleccionado?.nombre}</strong>?</p>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-secondary" onClick={() => setShowModalEliminar(false)}>Cancelar</button>
                <button type="button" className="btn btn-danger" onClick={confirmarEliminar}>Eliminar</button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
