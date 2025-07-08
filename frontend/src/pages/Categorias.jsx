import { useEffect, useRef, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import ModalForm from '../components/ModalForm';

import { useTienePermiso } from '../hooks/usePermisos';

export default function Categorias() {
  const puedeVer = useTienePermiso('ver_categorias');
  const puedeCrear = useTienePermiso('crear_categorias');
  const puedeEditar = useTienePermiso('editar_categorias');
  const puedeEliminar = useTienePermiso('eliminar_categorias');

  const [categorias, setCategorias] = useState([]);
  const [form, setForm] = useState({ nombre: '', descripcion: '' });
  const [editando, setEditando] = useState(null);

  const [busqueda, setBusqueda] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const inputRef = useRef(null);

  const [showModalEditar, setShowModalEditar] = useState(false);
  const [showModalEliminar, setShowModalEliminar] = useState(false);
  const [categoriaSeleccionada, setCategoriaSeleccionada] = useState(null);
  const [guardando, setGuardando] = useState(false); //estadoSubmit

  useEffect(() => {
    if (puedeVer) {
      obtenerCategorias();
    }
  }, [puedeVer]);

  useEffect(() => {
    if (inputRef.current) inputRef.current.focus();
  }, [editando]);

  const obtenerCategorias = async () => {
    try {
      const res = await api.get('/categorias');
      setCategorias(res.data);
    } catch {
      toast.error('Error al cargar categorías');
    }
  };

  const limpiarFormulario = () => {
    setForm({ nombre: '', descripcion: '' });
    setEditando(null);
    setTimeout(() => inputRef.current?.focus(), 200);
  };

  const handleGuardar = async (e) => {
    e.preventDefault();

    if (editando && !puedeEditar) {
      return toast.error('No tienes permiso para editar categorías');
    }

    if (!editando && !puedeCrear) {
      return toast.error('No tienes permiso para crear categorías');
    }

    const nombreTrim = form.nombre.trim();
    if (!nombreTrim) return toast.warning('El nombre es obligatorio');

    const existe = categorias.some(
      (cat) =>
        cat.nombre.toLowerCase() === nombreTrim.toLowerCase() &&
        (!editando || editando.id !== cat.id)
    );
    if (existe) return toast.warning('Ya existe una categoría con ese nombre');
    
    setGuardando(true);
    try {
      if (editando) {
        await api.put(`/categorias/${editando.id}`, form);
        toast.success('Categoría actualizada');
      } else {
        await api.post('/categorias', form);
        toast.success('Categoría creada');
      }
      obtenerCategorias();
      limpiarFormulario();
    } catch {
      toast.error('Error al guardar la categoría');
    } finally {
      setGuardando(false);
    }
    
  };

  const abrirModalEditar = (cat) => {
    if (showModalEditar) return; // Previene abrir varios modales accidentalmente
    if (!puedeEditar) return toast.error('No tienes permiso para editar');
    setCategoriaSeleccionada(cat);
    setShowModalEditar(true);
  };

  const confirmarEditar = () => {
    setEditando(categoriaSeleccionada);
    setForm({
      nombre: categoriaSeleccionada.nombre,
      descripcion: categoriaSeleccionada.descripcion || '',
    });
    setShowModalEditar(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const abrirModalEliminar = (cat) => {
    if (showModalEliminar) return; // Previene abrir varios modales accidentalmente
    if (!puedeEliminar) return toast.error('No tienes permiso para desactivar');
    setCategoriaSeleccionada(cat);
    setShowModalEliminar(true);
  };

  const confirmarEliminar = async () => {
    if (!puedeEliminar) return toast.error('No tienes permiso para desactivar');

    try {
      await api.delete(`/categorias/${categoriaSeleccionada.id}`);
      toast.success('Categoría desactivada');
      obtenerCategorias();
    } catch {
      toast.error('Error al desactivar');
    }
    setShowModalEliminar(false);
    setCategoriaSeleccionada(null);
  };

  const categoriasFiltradas = categorias.filter((cat) =>
    cat.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
    (cat.descripcion || '').toLowerCase().includes(busqueda.toLowerCase())
  );

  const totalPaginas = Math.ceil(categoriasFiltradas.length / porPagina);
  const categoriasPaginadas = categoriasFiltradas.slice(
    (pagina - 1) * porPagina,
    pagina * porPagina
  );

  if (!puedeVer) {
    return <div className="container mt-4"><p>No tienes permiso para ver esta sección.</p></div>;
  }

  return (
    <div className="container mt-4">
      <h4 className="mb-4">{editando ? 'Editar Categoría' : 'Registrar Nueva Categoría'}</h4>

      {/* Formulario */}
      { (puedeCrear || puedeEditar) && (
        <form onSubmit={handleGuardar} className="mb-4">
          <div className="row g-3 align-items-center">
            <div className="col-md-4">
              <input
                ref={inputRef}
                type="text"
                className="form-control"
                placeholder="Nombre de la categoría"
                value={form.nombre}
                onChange={(e) => setForm({ ...form, nombre: e.target.value })}
                required
              />
            </div>
            <div className="col-md-4">
              <input
                type="text"
                className="form-control"
                placeholder="Descripción (opcional)"
                value={form.descripcion}
                onChange={(e) => setForm({ ...form, descripcion: e.target.value })}
              />
            </div>
            <div className="col-md-4 d-flex gap-2">
              <button type="submit" className="btn btn-primary" disabled={guardando}>
                {editando ? 'Actualizar' : 'Registrar'}
              </button>
              {editando && (
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={limpiarFormulario}
                >
                  Cancelar
                </button>
              )}
            </div>
          </div>
        </form>
      )}

      {/* Buscar */}
      <div className="mb-3">
        <input
          type="text"
          className="form-control"
          placeholder="Buscar por nombre o descripción..."
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
      {/* Tabla */}
      <div className="table-responsive">
        <table className="table table-bordered table-hover align-middle">
          <thead className="table-light">
            <tr>
              <th>Nombre</th>
              <th>Descripción</th>
              {(puedeEditar || puedeEliminar) && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {categoriasPaginadas.map((cat, i) => (
              <tr key={cat.id}>
                <td>{cat.nombre}</td>
                <td>{cat.descripcion || '-'}</td>
                {(puedeEditar || puedeEliminar) && (
                  <td>
                    {puedeEditar && (
                      <button
                        className="btn btn-sm btn-warning me-2"
                        onClick={() => abrirModalEditar(cat)}
                      >
                        Editar
                      </button>
                    )}
                    {puedeEliminar && (
                      <button
                        className="btn btn-sm btn-danger"
                        onClick={() => abrirModalEliminar(cat)}
                      >
                        Desactivar
                      </button>
                    )}
                  </td>
                )}
              </tr>
            ))}
            {categoriasPaginadas.length === 0 && (
              <tr>
                <td colSpan={puedeEditar || puedeEliminar ? 4 : 3} className="text-center">
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

      {/* Modal Editar */}
      {showModalEditar && (
        <ModalForm
          title="Confirmar Edición"
          message={`¿Deseas editar la categoría  ${categoriaSeleccionada?.nombre}?`}
          onCancel={() => setShowModalEditar(false)}
          onConfirm={confirmarEditar}
          onType='editar'
        />
      )}
      {/* Modal Eliminar */}
      {showModalEliminar && (
        <ModalForm
          title="Confirmar Desactivación"
          message={`¿Deseas desactivar la categoría ${categoriaSeleccionada?.nombre}?`}
          onCancel={() => setShowModalEliminar(false)}
          onConfirm={confirmarEliminar}
          onType='desactivar'
        />
      )}

    </div>
  );
}
