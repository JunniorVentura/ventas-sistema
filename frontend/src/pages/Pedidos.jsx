// src/pages/Pedidos.jsx
import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import { useTienePermiso } from '../hooks/usePermisos';
import ModalForm from '../components/ModalForm';

export default function Pedidos() {
  const [pedidos, setPedidos] = useState([]);
  const [editando, setEditando] = useState(null);
  const [pedidoDetalle, setPedidoDetalle] = useState(null);
  const [showDetalle, setShowDetalle] = useState(false);
  const [showPagoModal, setShowPagoModal] = useState(false);
  const [showCancelarModal, setShowCancelarModal] = useState(false);
  const [metodoPago, setMetodoPago] = useState('efectivo');

  // Permisos
  const puedeVer = useTienePermiso("ver_pedidos");
  const puedeVerDetalles = useTienePermiso("ver_detalles_pedidos");
  const puedePagar = useTienePermiso("verificar_pagos");
  const puedeCancelar = useTienePermiso("verificar_pagos");
  const puedeEliminar = useTienePermiso("eliminar_pedidos");

  const [busqueda, setBusqueda] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  useEffect(() => {
    cargarPedidos();
  }, []);

  const cargarPedidos = async () => {
    try {
      const res = await api.get('/pedidos');
      setPedidos(res.data);
    } catch {
      toast.error('Error cargando pedidos');
    }
  };

  const iniciarPago = (pedido) => {
    if (!puedePagar) return toast.error("Sin permiso para pagar");
    setEditando(pedido);
    setShowPagoModal(true);
  };

  const confirmarPago = async () => {
    try {
      await api.post('/pagos', {
        pedido_id: editando.id,
        metodo_pago: metodoPago,
        estado_pago: 'pendiente'
      });
  
      await api.put(`/pedidos/${editando.id}`, {
        estado_pedido: 'pagado'
      });
  
      toast.success('Pago registrado correctamente');
      setShowPagoModal(false);
      cargarPedidos();
    } catch {
      toast.error('Error al registrar el pago');
    }
  };

  const iniciarCancelar = (pedido) => {
    if (!puedeCancelar) return toast.error("Sin permiso para cancelar");
    setEditando(pedido);
    setShowCancelarModal(true);
  };

  const confirmarCancelar = async () => {
    try {
      await api.put(`/pedidos/${editando.id}`, {
        estado_pedido: 'cancelado'
      });
      toast.success('Pedido cancelado');
      setShowCancelarModal(false);
      cargarPedidos();
    } catch {
      toast.error('Error al cancelar');
    }
  };

  const verDetalles = (pedido) => {
    if (!puedeVerDetalles) return toast.error("Sin permiso para ver detalles");
    setPedidoDetalle(pedido);
    setShowDetalle(true);
  };

  if (!puedeVer) {
    return (
      <div className="container mt-4">
        <p>No tienes permiso para ver esta sección.</p>
      </div>
    );
  }

  const pedidosFiltrados = pedidos.filter(p => {
    if (!busqueda) return true;
    return (
      p.id.toString().includes(busqueda) ||
      p.cliente?.dni?.toLowerCase().includes(busqueda.toLowerCase()) ||
      p.cliente?.nombre?.toLowerCase().includes(busqueda.toLowerCase())
    );
  });

  const totalPaginas = Math.ceil(pedidosFiltrados.length / porPagina);
  const pedidosPaginados = pedidosFiltrados.slice((pagina - 1) * porPagina, pagina * porPagina);
  
  return (
    <div className="container mt-4">
      <h3>Pedidos</h3>
      {/* Búsqueda */}
      <div className="mb-3">
        <input
          type="text"
          className="form-control"
          placeholder="Buscar por ID de pedido, DNI o cliente"
          value={busqueda}
          onChange={e => { setBusqueda(e.target.value); setPagina(1); }}
        />
      </div>
      {/*Select para paginación*/}
      <div className="mb-3 d-flex align-items-center">
        <label className="me-2">Pedidos por página:</label>
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
              <th>ID Pedido</th>
              <th>Cliente</th>
              <th>Usuario</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Metodo Pago</th>
              <th>Doc Emitidos</th>
              <th>Acciones</th>
              <th>Detalles</th>
            </tr>
          </thead>
          <tbody>
            {pedidosPaginados.map(p => (
              <tr key={p.id}>
                <td>{p.id}</td>
                <td>{p.cliente}</td>
                <td>{p.usuario}</td>
                <td>S/.{p.total}</td>
                <td>{p.estado_pedido}</td>
                <td>{p.pago?.metodo_pago ? p.pago?.metodo_pago : 'No especificado'}</td>
                <td>
                  {p.boleta_emitida ? ' Boleta' : ''}
                  {p.factura_emitida ? ' Factura' : ''}
                </td>
                <td>
                  {p.estado_pedido === 'pendiente' && puedePagar && (
                    <button
                      className="btn btn-sm btn-success me-1"
                      onClick={() => iniciarPago(p)}
                    >
                      Pagar
                    </button>
                  )}
                  {p.estado_pedido === 'pendiente' && puedeCancelar && (
                    <button
                      className="btn btn-sm btn-warning me-1"
                      onClick={() => iniciarCancelar(p)}
                    >
                      Cancelar
                    </button>
                  )}
                  {p.estado_pedido === 'pendiente' && puedeEliminar && (
                    <button
                      className="btn btn-sm btn-danger me-1"
                      onClick={() => iniciarCancelar(p)}
                      title="Eliminar"
                    >
                      <i className="bi bi-trash"></i>
                    </button>
                  )}
                </td>
                <td>
                  {puedeVerDetalles && (
                    <button
                      className="btn btn-sm btn-primary"
                      onClick={() => verDetalles(p)}
                      title="Ver detalles"
                    >
                      <i className="bi bi-eye"></i>
                    </button>
                  )}
                </td>
              </tr>
            ))}
              {pedidosPaginados.length === 0 && (
                <tr><td colSpan="5" className="text-center">No hay pedidos</td></tr>
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

      {/* Modal Pago personalizado con selección de método */}
      {showPagoModal && (
        <div className="modal show d-block" tabIndex="-1">
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">Registrar pago pedido #{editando.id}</h5>
                <button className="btn-close" onClick={() => setShowPagoModal(false)} />
              </div>
              <div className="modal-body">
                <p>Seleccione el método de pago:</p>
                <select
                  className="form-select"
                  value={metodoPago}
                  onChange={(e) => setMetodoPago(e.target.value)}
                >
                  <option value="efectivo">Efectivo</option>
                  <option value="yape">Yape</option>
                  <option value="transferencia">Transferencia</option>
                </select>
              </div>
              <div className="modal-footer">
                <button className="btn btn-secondary" onClick={() => setShowPagoModal(false)}>Cancelar</button>
                <button className="btn btn-primary" onClick={confirmarPago}>Confirmar</button>
              </div>
            </div>
          </div>
        </div>
      )}


      {/* Modal Cancelar */}
      {showCancelarModal && (
        <ModalForm
          title={`Cancelar pedido #${editando.id}`}
          message="¿Estás seguro de cancelarlo?"
          onCancel={() => setShowCancelarModal(false)}
          onConfirm={confirmarCancelar}
          onType="desactivar"
        />
      )}

      {showDetalle && pedidoDetalle && (
        <div className="modal show d-block" tabIndex="-1">
          <div className="modal-dialog modal-lg">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">Detalles pedido #{pedidoDetalle.id}</h5>
                <button className="btn-close" onClick={() => setShowDetalle(false)} />
              </div>
              <div className="modal-body">
                <p><strong>Cliente:</strong> {pedidoDetalle.cliente}</p>
                <p><strong>Usuario:</strong> {pedidoDetalle.usuario}</p>

                <table className="table">
                  <thead>
                    <tr><th>Artículo</th><th>Cant.</th><th>Precio</th><th>Total</th></tr>
                  </thead>
                  <tbody>
                    {(pedidoDetalle.detalle_pedidos?.length > 0) ? (
                      pedidoDetalle.detalle_pedidos.map(d => (
                        <tr key={d.id}>
                          <td>{d.producto?.nombre || 'Producto eliminado'}</td>
                          <td>{d.cantidad}</td>
                          <td>S/.{parseFloat(d.precio_unitario || 0).toFixed(2)}</td>
                          <td>S/.{parseFloat((d.precio_unitario * d.cantidad)|| 0).toFixed(2)}</td>
                        </tr>
                      ))
                    ) : (
                      <tr><td colSpan="4">Sin detalles</td></tr>
                    )}
                  </tbody>
                </table>

                <p><strong>Sub Total:</strong> S/.{parseFloat(pedidoDetalle.total || 0).toFixed(2)}</p>
                
              </div>
              <div className="modal-footer">
                <button className="btn btn-secondary" onClick={() => setShowDetalle(false)}>Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
