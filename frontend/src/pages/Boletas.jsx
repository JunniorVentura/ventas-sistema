// src/pages/Boletas.jsx
import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import { useTienePermiso } from '../hooks/usePermisos';

export default function Boletas() {
  const [boletas, setBoletas] = useState([]);
  const [pedidos, setPedidos] = useState([]);
  const [busqueda, setBusqueda] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const [mostrarModalDNI, setMostrarModalDNI] = useState(false);
  const [pedidoSeleccionado, setPedidoSeleccionado] = useState(null);
  const [dniCliente, setDniCliente] = useState('');

  // Permisos
  const puedeVer = useTienePermiso('ver_boletas');
  const puedeEmitir = useTienePermiso('crear_boletas');
  const [facturas, setFacturas] = useState([]);

   //paginación
  const [paginaPedidos, setPaginaPedidos] = useState(1);
  const [porPaginaPedidos, setPorPaginaPedidos] = useState(5);

  useEffect(() => {
    if (puedeVer) {
      cargarBoletas();
      cargarPedidos();
      cargarFacturas();
    }
  }, [puedeVer]);

  const cargarBoletas = async () => {
    try {
      const res = await api.get('/boletas');
      setBoletas(res.data);
    } catch {
      toast.error('Error cargando boletas');
    }
  };

  const cargarFacturas = async () => {
    try {
      const res = await api.get('/facturas');
      setFacturas(res.data);
    } catch {
      toast.error('Error cargando facturas');
    }
  };

  const cargarPedidos = async () => {
    try {
      const res = await api.get('/pedidos');
      setPedidos(res.data);
    } catch {
      toast.error('Error cargando pedidos');
    }
  };

  const pedidosEmitibles = pedidos.filter(p =>
    p.estado_pedido === 'pagado' &&
    p.pagos?.estado_pago === 'verificado' &&
    !p.boleta_emitida
  );
  
  const handleEmitir = (pedido) => {
    if (!puedeEmitir) {
      return toast.error('No tienes permiso para emitir boletas');
    }

    /*if (!pedido.cliente?.dni) {*/
      setPedidoSeleccionado(pedido);
      /*setDniCliente(pedido.cliente?.dni || '');*/
      setDniCliente(pedido.dni || '');
      setMostrarModalDNI(true);
    /*} else {
      emitirBoleta(pedido);
    }*/
  };

  const confirmarDNI = async () => {
    if (!puedeEmitir) return;

    if (!dniCliente || dniCliente.length !== 8 || !/^\d{8}$/.test(dniCliente)) {
      return toast.warning('Ingrese un DNI válido de 8 dígitos numéricos');
    }

    try {
      await api.put(`/clientes/${pedidoSeleccionado.cliente.id}`, {
        dni: dniCliente
      });

      setMostrarModalDNI(false);
      emitirBoleta({ ...pedidoSeleccionado, cliente: { ...pedidoSeleccionado.cliente, dni: dniCliente } });
    } catch {
      toast.error('Error actualizando DNI del cliente');
    }
  };

  const emitirBoleta = async (pedido) => {
    try {
      await api.post('/boletas', {
        pedido_id: pedido.id,
        cliente_id: pedido.cliente.id, // <-- nuevo
        dni_cliente: pedido.cliente.dni,        // <-- cambia de dni_cliente a dni
        nombre_cliente: pedido.cliente.nombre,
        total: pedido.total
      });
      toast.success('Boleta emitida');
      cargarBoletas();
      cargarPedidos();
    } catch {
      toast.error('Error al emitir boleta');
    }
  };

  const boletasFiltradas = boletas.filter(b =>
    b.id.toString().includes(busqueda) ||
    b.pedido_id?.toString().includes(busqueda) ||
    b.dni_cliente?.toLowerCase().includes(busqueda.toLowerCase()) ||
    b.nombre_cliente?.toLowerCase().includes(busqueda.toLowerCase())
  );

  const totalPaginas = Math.ceil(boletasFiltradas.length / porPagina);
  const boletasPaginadas = boletasFiltradas.slice((pagina - 1) * porPagina, pagina * porPagina);

  // Paginación basada en pedidosEmitibles
  const totalPaginasPedidos = Math.ceil(pedidosEmitibles.length / porPaginaPedidos);
  const pedidosPaginados = pedidosEmitibles.slice(
    (paginaPedidos - 1) * porPaginaPedidos,
    paginaPedidos * porPaginaPedidos
  );

  if (!puedeVer) {
    return <div className="container mt-4"><p>No tienes permiso para ver esta sección.</p></div>;
  }

  return (
    <div className="container mt-4">
      <h3>Boletas Emitidas</h3>
      {/* Búsqueda */}
      <div className="mb-3">
        <input
          type="text"
          className="form-control"
          placeholder="Buscar por ID de boleta, pedido, DNI o cliente"
          value={busqueda}
          onChange={e => { setBusqueda(e.target.value); setPagina(1); }}
        />
      </div>
      {/*Select para paginación*/}
      <div className="mb-3 d-flex align-items-center">
        <label className="me-2">Boletas por página:</label>
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
      {/* Tabla de boletas */}
      <div className="table-responsive">
        <table className="table table-hover">
          <thead className="table-dark">
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>DNI</th>
              <th>Total</th>
              <th>Fecha</th>
              <th>ID Pedido</th>
            </tr>
          </thead>
          <tbody>
            {boletasPaginadas.map(b => (
              <tr key={b.id}>
                <td>{b.id}</td>
                <td>{b.nombre_cliente}</td>
                <td>{b.dni_cliente}</td>
                <td>S/. {b.total}</td>
                <td>{new Date(b.fecha_emision).toLocaleString()}</td>
                <td>{b.pedido_id}</td>
              </tr>
            ))}
            {boletasPaginadas.length === 0 && (
              <tr><td colSpan="6" className="text-center">No hay boletas</td></tr>
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

      {/* Sección de pedidos sin boleta */}
      <div className="mt-5">
        <h4>Pedidos sin boleta (pagados y verificados)</h4>
        {/*Select para paginación*/}
        <div className="mb-3 d-flex align-items-center">
          <label className="me-2">Pedidos por página:</label>
          <select
            className="form-select w-auto"
            value={porPaginaPedidos}
            onChange={e => {
              setPorPaginaPedidos(parseInt(e.target.value));
              setPaginaPedidos(1); // Reinicia a la primera página
            }}
          >
            <option value={5}>5</option>
            <option value={10}>10</option>
            <option value={15}>15</option>
            <option value={20}>20</option>
          </select>
        </div>
        <div className="table-responsive">
          <table className="table table-striped table-sm">
            <thead>
              <tr>
                <th>ID Pedido</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado Pago</th>
                <th>Factura Emitida</th>
                <th>Emitir</th>
              </tr>
            </thead>
            <tbody>
              {pedidosPaginados.map(p => (
                <tr key={p.id}>
                  <td>{p.id}</td>
                  <td>{p.cliente}</td>
                  <td>S/. {p.total}</td>
                  <td>{p.pagos?.estado_pago}</td>
                  <td>
                    {p.factura_emitida ? (
                      <span className="badge bg-success">Sí</span>
                    ) : (
                      <span className="badge bg-secondary">No</span>
                    )}
                  </td>
                  <td>
                    {puedeEmitir && (
                      <button className="btn btn-sm btn-success" onClick={() => handleEmitir(p)}>
                        Emitir Boleta
                      </button>
                    )}
                  </td>
                </tr>
              ))}
              {pedidosPaginados.length === 0 && (
                <tr><td colSpan="6" className="text-center">No hay pedidos emitibles</td></tr>
              )}
            </tbody>
          </table>
        </div>
        {/*Paginación*/}
        {totalPaginasPedidos > 1 && (
          <div className="d-flex justify-content-center gap-2 mt-3">
            <button className="btn btn-outline-secondary"
              disabled={paginaPedidos === 1}
              onClick={() => setPaginaPedidos(paginaPedidos - 1)}>
              Anterior
            </button>
            <span className="align-self-center">Página {paginaPedidos} de {totalPaginasPedidos}</span>
            <button className="btn btn-outline-secondary"
              disabled={paginaPedidos === totalPaginasPedidos}
              onClick={() => setPaginaPedidos(paginaPedidos + 1)}>
              Siguiente
            </button>
          </div>
        )}

      </div>

      {/* Modal DNI */}
      {mostrarModalDNI && (
        <div className="modal show d-block" tabIndex="-1">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">DNI del Cliente</h5>
                <button className="btn-close" onClick={() => setMostrarModalDNI(false)} />
              </div>
              <div className="modal-body">
                <label>Ingrese el DNI del cliente:</label>
                <input
                  type="text"
                  className="form-control"
                  pattern="\d{8}"
                  maxLength={8}
                  value={dniCliente}
                  onChange={e => setDniCliente(e.target.value)}
                  placeholder=""
                />
              </div>
              <div className="modal-footer">
                <button className="btn btn-secondary" onClick={() => setMostrarModalDNI(false)}>Cancelar</button>
                <button className="btn btn-primary" onClick={confirmarDNI}>Guardar y Emitir</button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
