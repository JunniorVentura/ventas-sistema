// src/pages/Usuarios.jsx
import { useEffect, useRef, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import ModalForm from '../components/ModalForm';

import {
  useTienePermiso
} from '../hooks/usePermisos';

export default function Usuarios() {
  const puedeVer = useTienePermiso('ver_usuarios');
  const puedeCrear = useTienePermiso('crear_usuarios');
  const puedeEditar = useTienePermiso('editar_usuarios');
  const puedeEliminar = useTienePermiso('eliminar_usuarios');

  const [usuarios, setUsuarios] = useState([]);
  const [roles, setRoles] = useState([]);
  const [editando, setEditando] = useState(null);
  const [form, setForm] = useState({ nombre: '', email: '', rol_id: '', password: '' });

  const [busqueda, setBusqueda] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const inputNombreRef = useRef(null);
  const [showModalEditar, setShowModalEditar] = useState(false);
  const [showModalEliminar, setShowModalEliminar] = useState(false);
  const [usuarioSeleccionado, setUsuarioSeleccionado] = useState(null);
  const [guardando, setGuardando] = useState(false); //estadoSubmit

  useEffect(() => {
    if (puedeVer) {
      cargarUsuarios();
      cargarRoles();
    }
  }, [puedeVer]);

  useEffect(() => {
    if (inputNombreRef.current) inputNombreRef.current.focus();
  }, [editando]);

  const cargarUsuarios = async () => {
    try {
      const  res  = await api.get('/usuarios');
      setUsuarios(res.data);
    } catch {
      toast.error('Error al cargar usuarios');
    }
  };

  const cargarRoles = async () => {
    try {
      const res = await api.get('/roles');
      setRoles(res.data);
    } catch {
      toast.error('Error al cargar roles');
    }
  };

  const limpiarFormulario = () => {
    setForm({ nombre: '', email: '', rol_id: '', password: '' });
    setEditando(null);
    setTimeout(() => inputNombreRef.current?.focus(), 200);
  };

  const abrirModalEditar = (usuario) => {
    if (showModalEditar) return; // Previene abrir varios modales accidentalmente
    setUsuarioSeleccionado(usuario);
    setShowModalEditar(true);
  };

  const confirmarEditar = () => {
    setEditando(usuarioSeleccionado);
    setForm({
      nombre: usuarioSeleccionado.nombre,
      email: usuarioSeleccionado.email,
      rol_id: usuarioSeleccionado.rol_id,
      password: '',
    });
    setShowModalEditar(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => inputNombreRef.current?.focus(), 300);
  };

  const abrirModalEliminar = (usuario) => {
    if (showModalEliminar) return; // Previene abrir varios modales accidentalmente
    setUsuarioSeleccionado(usuario);
    setShowModalEliminar(true);
  };

  const confirmarEliminar = async () => {
    try {
      await api.delete(`/usuarios/${usuarioSeleccionado.id}`);
      toast.success('Usuario desactivado');
      cargarUsuarios();
    } catch {
      toast.error('Error al eliminar usuario');
    }
    setShowModalEliminar(false);
    setUsuarioSeleccionado(null);
  };

  const handleGuardar = async () => {
    if (!puedeCrear && !puedeEditar) {
      toast.error('No tienes permiso para esta acción');
      return;
    }
    if (!form.nombre || !form.email || !form.rol_id || (!editando && !form.password)) {
      return toast.warning('Completa todos los campos requeridos');
    }

    setGuardando(true);
    try {
      if (editando) {
        if (!puedeEditar) return toast.error('Sin permiso para editar');
        await api.put(`/usuarios/${editando.id}`, {
          nombre: form.nombre,
          email: form.email,
          rol_id: form.rol_id,
        });
        toast.success('Usuario actualizado');
      } else {
        if (!puedeCrear) return toast.error('Sin permiso para crear');
        await api.post('/usuarios', form);
        toast.success('Usuario creado');
      }
      cargarUsuarios();
      limpiarFormulario();
    } catch (err) {
      const msg = err.response?.data?.message || 'Error al guardar';
      toast.error(msg);
    } finally {
      setGuardando(false);
    }
  };

  if (!puedeVer) {
    return <div className="container mt-4"><p>No tienes permiso para ver usuarios.</p></div>;
  }

  const usuariosFiltrados = usuarios.filter((u) =>
    u.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
    u.email.toLowerCase().includes(busqueda.toLowerCase()) ||
    (u.rol?.nombre || '').toLowerCase().includes(busqueda.toLowerCase())
  );
  const totalPaginas = Math.ceil(usuariosFiltrados.length / porPagina);
  const usuariosPaginados = usuariosFiltrados.slice((pagina - 1) * porPagina, pagina * porPagina);

  return (
    <div className="container mt-4">
      <h4 className="mb-3">{editando ? 'Editar Usuario' : 'Registrar Nuevo Usuario'}</h4>

      {(puedeCrear || puedeEditar) && (
        <form onSubmit={(e) => e.preventDefault()}>
          <div className="row g-3 mb-4">
            <div className="col-md-3">
              <input
                ref={inputNombreRef}
                type="text"
                className="form-control"
                placeholder="Nombre"
                value={form.nombre}
                onChange={(e) => setForm({ ...form, nombre: e.target.value })}
                required
              />
            </div>
            <div className="col-md-3">
              <input
                type="email"
                className="form-control"
                placeholder="Correo"
                value={form.email}
                onChange={(e) => setForm({ ...form, email: e.target.value })}
                required
              />
            </div>
            <div className="col-md-3">
              <select
                className="form-select"
                value={form.rol_id}
                onChange={(e) => setForm({ ...form, rol_id: e.target.value })}
                required
              >
                <option value="">-- Seleccione Rol --</option>
                {roles.map((r) => (
                  <option key={r.id} value={r.id}>
                    {r.nombre}
                  </option>
                ))}
              </select>
            </div>
            {!editando && puedeCrear && (
              <div className="col-md-3">
                <input
                  type="password"
                  className="form-control"
                  placeholder="Contraseña"
                  value={form.password}
                  onChange={(e) => setForm({ ...form, password: e.target.value })}
                  required
                />
              </div>
            )}
          </div>
          <div className="mb-3 d-flex gap-2">
            <button className="btn btn-primary" onClick={handleGuardar} disabled={guardando}>
              {editando ? 'Actualizar' : 'Registrar'}
            </button>
            {editando && (
              <button className="btn btn-secondary" onClick={limpiarFormulario}>
                Cancelar
              </button>
            )}
          </div>
        </form>
      )}
      {/* Búsqueda */}
      <input
        type="text"
        className="form-control mb-3"
        placeholder="Buscar por nombre, correo o rol"
        value={busqueda}
        onChange={(e) => { setBusqueda(e.target.value); setPagina(1); }}
      />
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
      <div className="table-responsive">
        <table className="table table-bordered table-hover align-middle">
          <thead className="table-light">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Rol</th>
              {(puedeEditar || puedeEliminar) && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {usuariosPaginados.map((u, i) => (
              <tr key={u.id}>
                <td>{(pagina - 1) * porPagina + i + 1}</td>
                <td>{u.nombre}</td>
                <td>{u.email}</td>
                <td>{u.rol?.nombre || '-'}</td>
                {(puedeEditar || puedeEliminar) && (
                  <td>
                    {puedeEditar && (
                      <button className="btn btn-sm btn-warning me-2" onClick={() => abrirModalEditar(u)}>
                        Editar
                      </button>
                    )}
                    {puedeEliminar && (
                      <button className="btn btn-sm btn-danger" onClick={() => abrirModalEliminar(u)}>
                        Desactivar
                      </button>
                    )}
                  </td>
                )}
              </tr>
            ))}
            {usuariosPaginados.length === 0 && (
              <tr>
                <td colSpan="5" className="text-center">No hay usuarios encontrados</td>
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

      {/* Modales */}
      {showModalEditar && (
        <ModalForm
          title="Confirmar Edición"
          message={`¿Deseas editar al usuario ${usuarioSeleccionado?.nombre}?`}
          onCancel={() => setShowModalEditar(false)}
          onConfirm={confirmarEditar}
          onType='editar'
        />
      )}
      {showModalEliminar && (
        <ModalForm
          title="Confirmar Desactivación"
          message={`¿Deseas desactivar al usuario ${usuarioSeleccionado?.nombre}?`}
          onCancel={() => setShowModalEliminar(false)}
          onConfirm={confirmarEliminar}
          onType='desactivar'
        />
      )}
    </div>
  );
}
