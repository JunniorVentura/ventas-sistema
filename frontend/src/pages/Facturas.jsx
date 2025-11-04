// src/pages/Facturas.jsx
import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import { useTienePermiso } from '../hooks/usePermisos';

export default function Facturas() {
  const [facturas, setFacturas] = useState([]);
  const [pedidos, setPedidos] = useState([]);
  const [busqueda, setBusqueda] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const [mostrarModal, setMostrarModal] = useState(false);
  const [pedidoSeleccionado, setPedidoSeleccionado] = useState(null);
  const [rucCliente, setRucCliente] = useState('');
  const [razonSocial, setRazonSocial] = useState('');

  // Permisos
  const puedeVer = useTienePermiso('ver_facturas');
  const puedeEmitir = useTienePermiso('crear_facturas');
  const [boletas, setBoletas] = useState([]);

  //paginación
  const [paginaPedidos, setPaginaPedidos] = useState(1);
  const [porPaginaPedidos, setPorPaginaPedidos] = useState(5);

  useEffect(() => {
    if (puedeVer) {
      cargarFacturas();
      cargarPedidos();
      cargarBoletas();
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
      toast.error('Error al cargar facturas');
    }
  };

  const cargarPedidos = async () => {
    try {
      const res = await api.get('/pedidos');
      setPedidos(res.data);
    } catch {
      toast.error('Error al cargar pedidos');
    }
  };

  // Filtro primero
  const pedidosEmitibles = pedidos.filter(p =>
    p.estado_pedido === 'pagado' &&
    p.pagos?.estado_pago === 'verificado' &&
    !p.factura_emitida
  );
  
  const handleEmitir = (pedido) => {
    if (!puedeEmitir) {
      return toast.error('No tienes permiso para emitir facturas');
    }

    /*if (!pedido.cliente?.ruc || !pedido.cliente?.razon_social) {*/
      setPedidoSeleccionado(pedido);
      setRucCliente(pedido.cliente?.ruc || '');
      setRazonSocial(pedido.cliente?.razon_social || '');
      setMostrarModal(true);
    /*} else {
      emitirFactura(pedido);
    }*/
  };

  const confirmarRuc = async () => {
    if (!rucCliente || rucCliente.length !== 11 || !/^\d{11}$/.test(rucCliente)) {
      return toast.warning('Ingrese un RUC válido de 11 dígitos numéricos');
    }
    if (!razonSocial.trim()) {
      return toast.warning('Ingrese una razón social válida');
    }

    try {
      await api.put(`/clientes/${pedidoSeleccionado.cliente.id}`, {
        ruc: rucCliente,
        razon_social: razonSocial
      });

      setMostrarModal(false);
      emitirFactura({
        ...pedidoSeleccionado,
        cliente: {
          ...pedidoSeleccionado.cliente,
          ruc: rucCliente,
          razon_social: razonSocial
        }
      });
    } catch {
      toast.error('Error actualizando datos del cliente');
    }
  };

  const emitirFactura = async (pedido) => {
    try {
      await api.post('/facturas', {
        pedido_id: pedido.id,
        ruc_cliente: pedido.cliente.ruc,
        razon_social: pedido.cliente.razon_social,
        total: pedido.total
      });
      toast.success('Factura emitida correctamente');
      cargarFacturas();
      cargarPedidos();
    } catch {
      toast.error('Error al emitir factura');
    }
  };

  const facturasFiltradas = facturas.filter(f =>
    f.id.toString().includes(busqueda) ||
    f.pedido_id?.toString().includes(busqueda) ||
    f.ruc_cliente?.includes(busqueda) ||
    f.razon_social?.toLowerCase().includes(busqueda.toLowerCase())
  );

  const totalPaginas = Math.ceil(facturasFiltradas.length / porPagina);
  const facturasPaginadas = facturasFiltradas.slice((pagina - 1) * porPagina, pagina * porPagina);

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
      <h3>Facturas Emitidas</h3>

      {/* Buscador */}
      <div className="mb-3">
        <input
          type="text"
          className="form-control"
          placeholder="Buscar por ID, RUC, razón social o pedido"
          value={busqueda}
          onChange={e => { setBusqueda(e.target.value); setPagina(1); }}
        />
      </div>
      {/*Select para paginación*/}
      <div className="mb-3 d-flex align-items-center">
        <label className="me-2">Facturas por página:</label>
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
      {/* Tabla de Facturas */}
      <div className="table-responsive">
        <table className="table table-hover">
          <thead className="table-dark">
            <tr>
              <th>ID</th>
              <th>Razón Social</th>
              <th>RUC</th>
              <th>Total</th>
              <th>Fecha</th>
              <th>ID Pedido</th>
            </tr>
          </thead>
          <tbody>
            {facturasPaginadas.map(f => (
              <tr key={f.id}>
                <td>{f.id}</td>
                <td>{f.razon_social}</td>
                <td>{f.ruc_cliente}</td>
                <td>S/. {f.total}</td>
                <td>{new Date(f.fecha_emision).toLocaleString()}</td>
                <td>{f.pedido_id}</td>
              </tr>
            ))}
            {facturasPaginadas.length === 0 && (
              <tr><td colSpan="6" className="text-center">No hay facturas</td></tr>
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

      {/* Pedidos emitibles */}
      <div className="mt-5">
        <h4>Pedidos sin factura (pagados y verificados)</h4>
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
        <table className="table table-striped table-sm">
          <thead>
            <tr>
              <th>ID Pedido</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Estado Pago</th>
              <th>Boleta Emitida</th>
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
                  {p.boleta_emitida ? (
                    <span className="badge bg-success">Sí</span>
                  ) : (
                    <span className="badge bg-secondary">No</span>
                  )}
                </td>
                <td>
                  {puedeEmitir && (
                    <button className="btn btn-sm btn-success" onClick={() => handleEmitir(p)}>
                      Emitir Factura
                    </button>
                  )}
                </td>
              </tr>
            ))}
            {pedidosPaginados.length === 0 && (
              <tr><td colSpan="6" className="text-center">No hay pedidos disponibles</td></tr>
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
      {/* Modal para RUC y Razón Social */}
      {mostrarModal && (
        <div className="modal show d-block" tabIndex="-1">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">Datos del Cliente</h5>
                <button className="btn-close" onClick={() => setMostrarModal(false)} />
              </div>
              <div className="modal-body">
                <label>RUC del cliente:</label>
                <input
                  type="text"
                  className="form-control mb-2"
                  maxLength={11}
                  value={rucCliente}
                  onChange={e => setRucCliente(e.target.value)}
                />
                <label>Razón social:</label>
                <input
                  type="text"
                  className="form-control"
                  value={razonSocial}
                  onChange={e => setRazonSocial(e.target.value)}
                />
              </div>
              <div className="modal-footer">
                <button className="btn btn-secondary" onClick={() => setMostrarModal(false)}>Cancelar</button>
                <button className="btn btn-primary" onClick={confirmarRuc}>Guardar y Emitir</button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
