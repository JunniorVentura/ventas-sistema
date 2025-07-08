import React, { useEffect, useRef, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import { useTienePermiso } from '../hooks/usePermisos';

export default function Pagos() {
  const [pagos, setPagos] = useState([]);
  const [editando, setEditando] = useState(null);
  const [accion, setAccion] = useState(''); // 'verificar' o 'rechazar'
  const [busqueda, setBusqueda] = useState('');
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);
  const estadoRef = useRef(null);

  const [showModal, setShowModal] = useState(false);

  const puedeVer = useTienePermiso('ver_pagos');
  const puedeVerificar = useTienePermiso('verificar_pagos');
  const puedeEmitirDocumento = useTienePermiso('emitir_documento');

  const [showModalDocumento, setShowModalDocumento] = useState(false);
  const [tipoDocumento, setTipoDocumento] = useState('');
  const [dni, setDni] = useState('');
  const [ruc, setRuc] = useState('');
  const [razonSocial, setRazonSocial] = useState('');

  useEffect(() => {
    if (puedeVer) cargarPagos();
  }, [puedeVer]);

  const cargarPagos = async () => {
    try {
      const res = await api.get('/pagos');
      setPagos(res.data);
    } catch {
      toast.error('Error al cargar pagos');
    }
  };

  const abrirModal = (pago, tipo) => {
    if (!puedeVerificar) return toast.warning('Sin permiso para verificar pagos');
    setEditando(pago);
    setAccion(tipo);
    setShowModal(true);
  };

  const abrirModalDocumento = (pago, tipoDoc) => {
    if (!puedeEmitirDocumento) return toast.warning('Sin permiso para emitir documento');
    setEditando(pago);
    setTipoDocumento(tipoDoc);
    setShowModalDocumento(true);
  
     // Aquí ajusta según los datos reales que tengas en el pago:
    setDni(pago.dni || pago.cliente_dni || '');
    setRuc(pago.ruc || pago.cliente_ruc || '');
    setRazonSocial(pago.razon_social || pago.cliente_razon_social || '');
  };

  const emitirDocumento = async () => {
    if (!puedeEmitirDocumento) return toast.warning('Sin permiso para emitir documento');
    if (tipoDocumento === 'boleta' && !dni) {
      toast.error('El cliente debe tener DNI para emitir boleta');
      return;
    }
    if (tipoDocumento === 'factura' && (!ruc || !razonSocial)) {
      toast.error('El cliente debe tener RUC y razón social para emitir factura');
      return;
    }
  
    try {
      if (tipoDocumento === 'boleta') {
        await api.post('/boletas', {
          pedido_id: editando.pedido_id,
          cliente_id: editando.cliente_id,
          dni_cliente: dni,
          nombre_cliente: editando.cliente || 'Cliente',
          total: editando.monto_pago
        });
        toast.success('Boleta emitida');
      } else if (tipoDocumento === 'factura') {
        await api.post('/facturas', {
          pedido_id: editando.pedido_id,
          ruc_cliente: ruc,
          razon_social: razonSocial,
          total: editando.monto_pago
        });
        toast.success('Factura emitida');
      }
  
      setShowModalDocumento(false);
      cargarPagos();
    } catch {
      toast.error('Error al emitir documento');
    }
  };  

  const procesarAccion = async () => {
    let nuevoEstado = '';
    if (accion === 'verificar') nuevoEstado = 'verificado';
    else if (accion === 'rechazar') nuevoEstado = 'rechazado';

    if (!nuevoEstado) return;

    try {
      await api.put(`/pagos/${editando.id}`, { estado_pago: nuevoEstado });
      toast.success(`Pago ${nuevoEstado}`);
      setShowModal(false);
      setEditando(null);
      cargarPagos();
    } catch {
      toast.error('Error al procesar acción');
    }
  };

  const pagosFiltrados = pagos.filter(p =>
    p.metodo_pago?.toLowerCase().includes(busqueda.toLowerCase()) ||
    p.estado_pago?.toLowerCase().includes(busqueda.toLowerCase()) ||
    String(p.pedido_id).includes(busqueda) ||
    p.cliente?.toLowerCase().includes(busqueda.toLowerCase()) ||
    p.usuario?.toLowerCase().includes(busqueda.toLowerCase()) ||
    p.dni?.includes(busqueda) ||
    p.ruc?.includes(busqueda) ||
    p.razon_social?.toLowerCase().includes(busqueda.toLowerCase())
  );  

  const totalPaginas = Math.ceil(pagosFiltrados.length / porPagina);
  const pagosPaginados = pagosFiltrados.slice((pagina - 1) * porPagina, pagina * porPagina);

  if (!puedeVer) return (<div className="container mt-4"><p>No tienes permiso.</p></div>);

  return (
    <div className="container mt-4">
      <h3 className="mb-3">Gestión de Pagos</h3>
      {/* Búsqueda */}
      <div className="mb-3">
        <input
          className="form-control"
          placeholder="Buscar por pedido, método, estado, cliente, usuario, DNI, RUC o razón social"
          value={busqueda}
          onChange={e => {
            setBusqueda(e.target.value);
            setPagina(1); // Reinicia la paginación al buscar
          }}
        />
      </div>
      {/*Select para paginación*/}
      <div className="mb-3 d-flex align-items-center">
        <label className="me-2">Pagos por página:</label>
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
              <th>Pedido</th>
              <th>Cliente</th>
              <th>Usuario</th>
              <th>Método</th>
              <th>Estado</th>
              <th>Fecha pago</th>
              <th>Monto Total</th>
              {puedeVerificar && <th>Acciones</th>}
              {puedeEmitirDocumento && <th>Emitir documento</th>}
            </tr>
          </thead>
          <tbody>
            {pagosPaginados.map(p => (
              <tr key={p.id}>
                <td>{p.pedido_id}</td>
                <td>{p.cliente || p.cliente_id}</td>
                <td>{p.usuario || p.usuario_id}</td>
                <td>{p.metodo_pago}</td>
                <td>{p.estado_pago}</td>
                <td>{new Date(p.fecha_pago).toLocaleDateString()}</td>
                <td>S/. {parseFloat(p.monto_pago || 0).toFixed(2)}</td>
                {puedeVerificar && (
                  <td>
                    {p.estado_pago === 'pendiente' ? (
                      <>
                          <button className="btn btn-sm btn-success me-2" onClick={() => abrirModal(p,'verificar')}>
                            Verificar
                          </button>
                          <button className="btn btn-sm btn-success me-2" onClick={() => abrirModal(p,'rechazar')}>
                            Rechazar
                          </button>
                      </>
                    ) : <span>—</span>}
                  </td>
                )}
                {puedeEmitirDocumento && (
                  <td>
                    {p.estado_pago === 'verificado' ? (
                      <>
                        {!p.boleta_emitida && (
                          <button className="btn btn-sm btn-success me-2" onClick={() => abrirModalDocumento(p, 'boleta')}>
                            Emitir Boleta
                          </button>
                        )}
                        {!p.factura_emitida && (
                          <button className="btn btn-sm btn-success me-2" onClick={() => abrirModalDocumento(p, 'factura')}>
                            Emitir Factura
                          </button>
                        )}
                        {p.boleta_emitida && p.factura_emitida && <span>—</span>}
                      </>
                    ) : <span>—</span>}
                  </td>
                )}
              </tr>
            ))}
            {pagosPaginados.length === 0 && (
              <tr><td colSpan={puedeVerificar ? 8 : 7} className="text-center">No hay registros</td></tr>
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

      {showModal && (
        <div className="modal show d-block" tabIndex="-1">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">{accion === 'verificar' ? 'Verificar' : 'Rechazar'} Pago #{editando.id}</h5>
                <button type="button" className="btn-close" onClick={() => setShowModal(false)}></button>
              </div>
              <div className="modal-body">
                <p>¿Seguro que deseas <strong>{accion}</strong> este pago?</p>
                <ul>
                  <li><strong>Fecha Pedido:</strong> {editando.fecha_pedido}</li>
                  <li><strong>ID Pedido:</strong> {editando.pedido_id}</li>
                  <li><strong>Cliente:</strong> {editando.cliente || editando.cliente_id}</li>
                  <li><strong>Usuario:</strong> {editando.usuario || editando.usuario_id}</li>
                  <li><strong>Método:</strong> {editando.metodo_pago}</li>
                  <li><strong>Monto Total:</strong> S/. {parseFloat(editando.monto_pago || 0).toFixed(2)}</li>
                </ul>
              </div>
              <div className="modal-footer">
                <button className="btn btn-secondary" onClick={() => setShowModal(false)}>Cancelar</button>
                <button className={`btn ${accion === 'verificar' ? 'btn-success' : 'btn-danger'}`} onClick={procesarAccion}>
                  {accion === 'verificar' ? 'Verificar' : 'Rechazar'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {showModalDocumento && (
        <div className="modal show d-block" tabIndex="-1">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">Emitir {tipoDocumento}</h5>
                <button type="button" className="btn-close" onClick={() => setShowModalDocumento(false)}></button>
              </div>
              <div className="modal-body">
                <p><strong>Cliente:</strong> {editando.cliente || editando.cliente_id}</p>
                {tipoDocumento === 'boleta' && (
                  <div className="mb-3">
                    <label className="form-label">DNI</label>
                    <input className="form-control" value={dni} onChange={e => setDni(e.target.value)} maxLength={8} />
                  </div>
                )}
                {tipoDocumento === 'factura' && (
                  <>
                    <div className="mb-3">
                      <label className="form-label">RUC</label>
                      <input className="form-control" value={ruc} onChange={e => setRuc(e.target.value)} maxLength={11} />
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Razón Social</label>
                      <input className="form-control" value={razonSocial} onChange={e => setRazonSocial(e.target.value)} />
                    </div>
                  </>
                )}
              </div>
              <div className="modal-footer">
                <button className="btn btn-secondary" onClick={() => setShowModalDocumento(false)}>Cancelar</button>
                <button className="btn btn-success" onClick={emitirDocumento}>Emitir</button>
              </div>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
