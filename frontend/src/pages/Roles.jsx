import { useEffect, useRef, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import ModalForm from '../components/ModalForm';

import {
  useTienePermiso
} from '../hooks/usePermisos';

export default function Roles() {
  const puedeVer = useTienePermiso('ver_roles');
  const puedeCrear = useTienePermiso('crear_roles');
  const puedeEditar = useTienePermiso('editar_roles');
  const puedeEliminar = useTienePermiso('eliminar_roles');

  const [roles, setRoles] = useState([]);
  const [nombre, setNombre] = useState('');
  const [descripcion, setDescripcion] = useState('');
  const [editando, setEditando] = useState(null);

  const [busqueda, setBusqueda] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const inputNombreRef = useRef(null);

  const [showModalEditar, setShowModalEditar] = useState(false);
  const [showModalEliminar, setShowModalEliminar] = useState(false);
  const [rolSeleccionado, setRolSeleccionado] = useState(null);

  useEffect(() => {
    if (puedeVer) {
      obtenerRoles();
    }
  }, [puedeVer]);

  const obtenerRoles = async () => {
    try {
      const res = await api.get('/roles');
      setRoles(res.data);
    } catch (error) {
      toast.error('Error al obtener los roles');
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
    if (!puedeCrear && !puedeEditar) {
      return toast.error('No tienes permiso para esta acción');
    }

    try {
      if (editando) {
        if (!puedeEditar) return toast.error('No tienes permiso para editar');
        await api.put(`/roles/${editando}`, { nombre, descripcion });
        toast.success('Rol actualizado');
      } else {
        if (!puedeCrear) return toast.error('No tienes permiso para crear');
        await api.post('/roles', { nombre, descripcion });
        toast.success('Rol creado');
      }
      limpiarFormulario();
      obtenerRoles();
    } catch (err) {
      toast.error('Error al guardar el rol');
    }
  };

  const abrirModalEditar = (rol) => {
    setRolSeleccionado(rol);
    setShowModalEditar(true);
  };

  const confirmarEditar = () => {
    setEditando(rolSeleccionado.id);
    setNombre(rolSeleccionado.nombre);
    setDescripcion(rolSeleccionado.descripcion || '');
    setShowModalEditar(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => inputNombreRef.current?.focus(), 300);
  };

  const abrirModalEliminar = (rol) => {
    setRolSeleccionado(rol);
    setShowModalEliminar(true);
  };

  const confirmarEliminar = async () => {
    if (!puedeEliminar) return toast.error('No tienes permiso para eliminar');
    try {
      await api.delete(`/roles/${rolSeleccionado.id}`);
      toast.success('Rol desactivado');
      obtenerRoles();
    } catch (err) {
      toast.error('Error al desactivar el rol');
    }
    setShowModalEliminar(false);
    setRolSeleccionado(null);
  };

  if (!puedeVer) {
    return <div className="container mt-4"><p>No tienes permiso para ver roles.</p></div>;
  }

  const rolesFiltrados = roles.filter((rol) =>
    rol.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
    (rol.descripcion || '').toLowerCase().includes(busqueda.toLowerCase())
  );
  const totalPaginas = Math.ceil(rolesFiltrados.length / porPagina);
  const rolesPaginados = rolesFiltrados.slice(
    (pagina - 1) * porPagina,
    pagina * porPagina
  );

  return (
    <div className="container mt-4">
      <h4 className="mb-3">{editando ? 'Editar Rol' : 'Registrar Nuevo Rol'}</h4>

      {(puedeCrear || puedeEditar) && (
        <form onSubmit={handleSubmit} className="mb-4">
          <div className="row g-3 align-items-center">
            <div className="col-md-4">
              <input
                type="text"
                className="form-control"
                placeholder="Nombre del rol"
                value={nombre}
                onChange={(e) => setNombre(e.target.value)}
                ref={inputNombreRef}
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
              <button className="btn btn-primary" type="submit">
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
          placeholder="Buscar rol..."
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
      <table className="table table-bordered table-hover">
        <thead className="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            {(puedeEditar || puedeEliminar) && <th>Acciones</th>}
          </tr>
        </thead>
        <tbody>
          {rolesPaginados.map((rol) => (
            <tr key={rol.id}>
              <td>{rol.id}</td>
              <td>{rol.nombre}</td>
              <td>{rol.descripcion || '-'}</td>
              {(puedeEditar || puedeEliminar) && (
                <td>
                  {puedeEditar && (
                    <button
                      className="btn btn-sm btn-warning me-2"
                      onClick={() => abrirModalEditar(rol)}
                    >
                      Editar
                    </button>
                  )}
                  {puedeEliminar && (
                    <button
                      className="btn btn-sm btn-danger"
                      onClick={() => abrirModalEliminar(rol)}
                    >
                      Desactivar
                    </button>
                  )}
                </td>
              )}
            </tr>
          ))}
          {rolesPaginados.length === 0 && (
            <tr>
              <td colSpan="4" className="text-center">
                No hay resultados
              </td>
            </tr>
          )}
        </tbody>
      </table>


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
      
      {/* Modales */}
      {/* Modal Confirmar Edición */}
      {showModalEditar && (
        <ModalForm
          title="Confirmar Edición"
          message={`¿Deseas editar al rol ${rolSeleccionado?.nombre}?`}
          onCancel={() => setShowModalEditar(false)}
          onConfirm={confirmarEditar}
          onType='editar'
        />
      )}
      {/* Modal Confirmar Eliminación */}
      {showModalEliminar && (
        <ModalForm
          title="Confirmar Desactivación"
          message={`¿Deseas desactivar el rol ${rolSeleccionado?.nombre}?`}
          onCancel={() => setShowModalEliminar(false)}
          onConfirm={confirmarEliminar}
          onType='desactivar'
        />
      )}
    </div>
  );
}
