// src/pages/Stock.jsx
import { useEffect, useRef, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import ModalForm from '../components/ModalForm';
import { useTienePermiso } from '../hooks/usePermisos';

export default function Stock() {
  const puedeVer = useTienePermiso("ver_stock");
  const puedeActualizar = useTienePermiso("actualizar_stock");

  const [stocks, setStocks] = useState([]);
  const [productos, setProductos] = useState([]);

  const [editando, setEditando] = useState(null);
  const [stockSeleccionado, setStockSeleccionado] = useState(null);
  const [showModalEditar, setShowModalEditar] = useState(false);

  const [form, setForm] = useState({ producto_id: '', cantidad: '' });

  const [busquedaStock, setBusquedaStock] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const cantidadRef = useRef(null);

  const [guardando, setGuardando] = useState(false); //estadoSubmit

  useEffect(() => {
    if (puedeVer) cargarDatos();
  }, [puedeVer]);

  const cargarDatos = async () => {
    try {
      const [resProductos, resStock] = await Promise.all([
        api.get('/productos'),
        api.get('/stock')
      ]);
      setProductos(resProductos.data);
      setStocks(resStock.data);
    } catch {
      toast.error('Error al cargar productos o stock.');
    }
  };

  const abrirModalEditar = (stockItem) => {
    if (showModalEditar) return;
    if (!puedeActualizar) return toast.error("No tienes permiso para editar stock.");
    setStockSeleccionado(stockItem);
    setShowModalEditar(true);
  };

  const confirmarEditar = () => {
    setEditando(stockSeleccionado);
    setForm({
      producto_id: stockSeleccionado.producto.id,
      cantidad: stockSeleccionado.cantidad
    });
    setShowModalEditar(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => cantidadRef.current?.focus(), 300);
  };

  const handleGuardar = async (e) => {
    e.preventDefault();

    if (!form.producto_id || form.cantidad === '') {
      return toast.warning('Selecciona producto y cantidad.');
    }

    if (!puedeActualizar) return toast.error("No tienes permiso para actualizar stock.");
    
    setGuardando(true);
    try {
      await api.put(`/stock/${editando.id}`, form);
      toast.success('Stock actualizado');
      limpiarFormulario();
      cargarDatos();
    } catch {
      toast.error('Hubo un error al guardar.');
    } finally {
      setGuardando(false);
    }
  };

  const limpiarFormulario = () => {
    setEditando(null);
    setForm({ producto_id: '', cantidad: '' });
  };

  const stocksFiltrados = stocks.filter(s =>
    s.producto.nombre.toLowerCase().includes(busquedaStock.toLowerCase())
  );

  const totalPaginas = Math.ceil(stocksFiltrados.length / porPagina);
  const stocksPaginados = stocksFiltrados.slice((pagina - 1) * porPagina, pagina * porPagina);

  if (!puedeVer) {
    return <div className="container mt-4"><p>No tienes permiso para ver esta sección.</p></div>;
  }

  return (
    <div className="container mt-4">
      <h4 className="mb-3">Stock de Productos</h4>
      {/* Búsqueda */}
      {(editando && puedeActualizar) && (
        <form onSubmit={handleGuardar} className="mb-4">
          <div className="row g-3 align-items-center">
            <div className="col-md-4">
              <input
                type="text"
                className="form-control"
                value={productos.find(p => p.id === form.producto_id)?.nombre || ''}
                disabled
              />
            </div>
            <div className="col-md-4">
              <input
                ref={cantidadRef}
                type="number"
                className="form-control"
                placeholder="Cantidad"
                value={form.cantidad}
                onChange={(e) => setForm({ ...form, cantidad: e.target.value })}
                required
                min="0"
              />
            </div>
            <div className="col-md-4 d-flex gap-2">
              <button className="btn btn-primary" type="submit" disabled={guardando}>Actualizar</button>
              <button className="btn btn-secondary" type="button" onClick={limpiarFormulario}>
                Cancelar
              </button>
            </div>
          </div>
        </form>
      )}
      {/* Búsqueda */}
      <div className="mb-3">
        <input
          type="text"
          className="form-control"
          placeholder="Buscar producto por nombre..."
          value={busquedaStock}
          onChange={(e) => {
            setBusquedaStock(e.target.value);
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
      <div className="table-responsive">
        <table className="table table-hover align-middle">
          <thead className="table-dark">
            <tr>
              {/*<th>#</th>*/}
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Actualizado</th>
              {puedeActualizar && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {stocksPaginados.map((s, i) => (
              <tr key={s.id}>
                {/*<td>{(pagina - 1) * porPagina + i + 1}</td>*/}
                <td>{s.producto.nombre}</td>
                <td>{s.cantidad}</td>
                <td>{new Date(s.updated_at).toLocaleDateString()}</td>
                {puedeActualizar && (
                  <td>
                    <button className="btn btn-sm btn-warning" onClick={() => abrirModalEditar(s)}>
                      Editar
                    </button>
                  </td>
                )}
              </tr>
            ))}
            {stocksPaginados.length === 0 && (
              <tr>
                <td colSpan={5} className="text-center">No hay resultados</td>
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

      {/* Modal Confirmar Edición */}
      {showModalEditar && (
        <ModalForm
          title="Confirmar Edición de Stock"
          message={`¿Deseas editar el stock del producto "${stockSeleccionado?.producto?.nombre}"?`}
          onCancel={() => setShowModalEditar(false)}
          onConfirm={confirmarEditar}
          onType="editar"
        />
      )}
    </div>
  );
}
