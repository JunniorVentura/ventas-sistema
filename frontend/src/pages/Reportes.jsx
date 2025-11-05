import React, { useState, useEffect } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import axios from 'axios';
import { useTienePermiso } from '../hooks/usePermisos';

export default function Reportes() {
  const [desde, setDesde] = useState('2020-01-01');
  const [hasta, setHasta] = useState(() => {
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0'); // Mes con 2 dígitos
    const dd = String(hoy.getDate()).padStart(2, '0');      // Día con 2 dígitos
    return `${yyyy}-${mm}-${dd}`; // Formato YYYY-MM-DD
  });
  const [ventas, setVentas] = useState([]);
  const [productosTop, setProductosTop] = useState([]);
  const [estadoPago, setEstadoPago] = useState('verificado');
  const [pagos, setPagos] = useState([]);
  const [anioBoleta, setAnioBoleta] = useState(new Date().getFullYear());
  const [anio, setAnio] = useState(new Date().getFullYear());
  const [mesBoleta, setMesBoleta] = useState(new Date().getMonth() + 1);
  const [mes, setMes] = useState(new Date().getMonth() + 1);

  const [facturasCount, setFacturasCount] = useState(0);
  const [boletasCount, setBoletasCount] = useState(0);
  
  // Permisos
  const puedeVerReportes = useTienePermiso('ver_reportes');
  const puedeExportarReportes = useTienePermiso('exportar_reportes');

  useEffect(() => {
    if (puedeVerReportes) {
      buscarVentas();       // buscar ventas automáticamente
      buscarTopProductos();
      buscarPagos();
    }
  }, [estadoPago, puedeVerReportes]);

  useEffect(() => {
    if (puedeVerReportes) {
      contarBoletas();
    }
  }, [anioBoleta, mesBoleta, puedeVerReportes]);  

  useEffect(() => {
    if (puedeVerReportes) {
      contarFacturas();
    }
  }, [anio, mes, puedeVerReportes]);  

  const contarFacturas = async () => {
    //console.log({ anio, mes });  // Asegúrate que son números válidos

    try {
      const res = await api.get(`/reportes/facturas-count/${anio}/${mes}`);
      setFacturasCount(res.data.total);
    } catch (error) {
      console.error('Error al obtener cantidad de facturas:', error.response?.data || error);
      toast.error('Error al obtener cantidad de facturas');
    }
    
  };

  const contarBoletas = async () => {
    //console.log({ anioBoleta, mesBoleta });  // Asegúrate que son números válidos

    try {
      const res = await api.get(`/reportes/boletas-count/${anioBoleta}/${mesBoleta}`);
      setBoletasCount(res.data.total);
    } catch (error) {
      console.error('Error al obtener cantidad de boletas:', error.response?.data || error);
      toast.error('Error al obtener cantidad de boletas');
    }
    
  };

  const buscarVentas = async () => {
    if (!desde || !hasta) {
      toast.warning('Seleccione rango de fechas');
      return;
    }
    try {
      const res = await api.get('/reportes/ventas', {
        params: { desde, hasta }
      });
      setVentas(res.data);
    } catch {
      toast.error('Error al obtener ventas');
    }
  };
  
  const buscarTopProductos = async () => {
    try {
      const res = await api.get('/reportes/productos-mas-vendidos');
      setProductosTop(res.data);
    } catch {
      toast.error('Error al obtener productos');
    }
  };

  const buscarPagos = async () => {
    try {
      const res = await api.get(`/reportes/pagos/${estadoPago}`);
      setPagos(res.data);
    } catch (error) {
      console.error('Error al obtener pagos:', error);
      toast.error('Error al obtener pagos');
    }
  };
  
  const descargarPDF = async (url, nombreBase = 'reporte') => {
    if (!puedeExportarReportes) {
      toast.error('No tienes permisos para exportar PDF');
      return;
    }
  
    try {
      const res = await api.get(url, { responseType: 'blob' });
      const file = new Blob([res.data], { type: 'application/pdf' });
      const urlBlob = window.URL.createObjectURL(file);
  
      // Generar nombre único con fecha y hora
      const ahora = new Date();
      const timestamp = `${ahora.getFullYear()}-${String(ahora.getMonth() + 1).padStart(2, '0')}-${String(ahora.getDate()).padStart(2, '0')}_${String(ahora.getHours()).padStart(2, '0')}-${String(ahora.getMinutes()).padStart(2, '0')}-${String(ahora.getSeconds()).padStart(2, '0')}`;
      const nombreFinal = `${nombreBase}_${timestamp}.pdf`;
  
      // Abrir pestaña nueva con el PDF y el botón para descargar
      const nuevaPestaña = window.open('', '_blank');
      nuevaPestaña.document.write(`
        <html>
          <head>
            <title>Vista previa PDF</title>
          </head>
          <body style="margin:0">
            <embed src="${urlBlob}" type="application/pdf" width="100%" height="100%"/>
            <a href="${urlBlob}" title="Descargar: ${nombreFinal}" download="${nombreFinal}" style="
              position: fixed;
              top: 10px;
              right: 10px;
              padding: 10px 15px;
              background: #616161;
              color: #fff;
              text-decoration: none;
              border-radius: 4px;
              z-index: 9999;
            ">Descargar PDF</a>
          </body>
        </html>
      `);
  
      // Limpieza
      setTimeout(() => window.URL.revokeObjectURL(urlBlob), 60000);
  
    } catch (error) {
      toast.error('No se pudo descargar ni abrir el PDF');
      console.error('Error al descargar PDF:', error);
    }
  };
  
  
  /* Descargar con nombre aleatorio
  const descargarPDF = async (url, nombre = 'reporte.pdf') => {
    if (!puedeExportarReportes) {
      toast.error('No tienes permisos para exportar PDF');
      return;
    }
    
      try {
        const res = await api.get(url, { responseType: 'blob' });
        const file = new Blob([res.data], { type: 'application/pdf' });
    
        // Crear URL temporal del PDF
        const urlBlob = window.URL.createObjectURL(file);
    
        // Abre en una nueva pestaña (vista previa)
        window.open(urlBlob, '_blank');
    
        // Descargar el PDF automáticamente (descomentar si es necesario)
        /*const link = document.createElement('a');
        link.href = urlBlob;
        link.download = nombre;
        document.body.appendChild(link);
        link.click();
        link.remove();*/
    
        // Limpieza: Revocar URL temporal después de un tiempo*/
        /*setTimeout(() => window.URL.revokeObjectURL(urlBlob), 1000);
      } catch (error) {
        toast.error('No se pudo descargar ni abrir el PDF');
        console.error('Error al descargar PDF:', error);
      }
    };*/

  if (!puedeVerReportes) {
    return <div className="container mt-4"><p>No tienes permiso para ver reportes.</p></div>;
  }

  return (
    <div className="container mt-4">
      <h3> Reportes del Sistema</h3>

      {/* Reporte de Ventas por Fecha */}
      <div className="card mb-4">
        <div className="card-header bg-primary text-white">Ventas por Fecha</div>
        <div className="card-body">
          <div className="row g-2 mb-2">
            <div className="col-md-4">
              <input type="date" className="form-control" value={desde} onChange={(e) => setDesde(e.target.value)} />
            </div>
            <div className="col-md-4">
              <input type="date" className="form-control" value={hasta} onChange={(e) => setHasta(e.target.value)} />
            </div>
            <div className="col-md-4 d-flex gap-2">
              <button className="btn btn-primary" onClick={buscarVentas}>Buscar</button>
              {puedeExportarReportes && (
                <button className="btn btn-success" onClick={() => descargarPDF(`/reportes/pdf/ventas?desde=${desde}&hasta=${hasta}`, 'reporte_ventas')}>PDF</button>
              )}
            </div>
          </div>
          {ventas.length > 0 && (
          <table className="table table-hover align-middle">
            <thead className="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>Total</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                {ventas.map(v => (
                  <tr key={v.id}>
                    <td>{v.id}</td>
                    <td>{v.cliente?.nombre}</td>
                    <td>S/. {v.total}</td>
                    <td>{new Date(v.fecha).toLocaleString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>

      {/* Productos Más Vendidos */}
      <div className="card mb-4">
        <div className="card-header bg-warning">Top Productos Más Vendidos</div>
        <div className="card-body">
          {puedeExportarReportes && (
            <button className="btn btn-outline-danger mb-3" onClick={() => descargarPDF('/reportes/pdf/productos-mas-vendidos', 'reporte_top_productos')}> Descargar PDF</button>
          )}
          <table className="table table-hover align-middle">
            <thead className="table-dark">
              <tr>
              <th>Producto</th>
              <th>Total Vendido</th>
              </tr>
            </thead>
            <tbody>
              {productosTop.map((p, i) => (
                <tr key={i}>
                  <td>{p.nombre || p.producto_id}</td>
                  <td>{p.total_vendido}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Pagos por Estado */}
      <div className="card mb-4">
        <div className="card-header bg-info text-white">Pagos por Estado</div>
        <div className="card-body">
          <div className="mb-3">
            <select className="form-select" value={estadoPago} onChange={(e) => setEstadoPago(e.target.value)}>
              <option value="verificado">Verificado</option>
              <option value="pendiente">Pendiente</option>
              <option value="rechazado">Rechazado</option>
            </select>
          </div>
          {puedeExportarReportes && (
            <button
              className="btn btn-danger mb-3"
              onClick={() => descargarPDF(`/reportes/pdf/pagos/${estadoPago}`, `pagos-${estadoPago}`)}>
              Descargar PDF
            </button>
         )}
          <table className="table table-hover align-middle">
            <thead className="table-dark">
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              {pagos.map((pago) => {
                const cliente = pago.pedido?.cliente;
                return (
                  <tr key={pago.id}>
                    <td>{pago.id}</td>
                    <td>{cliente?.nombre ?? 'Sin cliente'}</td>
                    <td>S/. {pago.pedido?.total}</td>
                    <td>{pago.estado_pago}</td>
                    <td>{new Date(pago.fecha_pago).toLocaleString()}</td>
                  </tr>
                );
              })}
              {pagos.length === 0 && (
                <tr><td colSpan="5" className="text-center">No hay pagos</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Boletas por Mes */}
      <div className="card mb-4">
        <div className="card-header bg-secondary text-white">Boletas por Mes</div>
        <div className="card-body row g-2">
          <div className="col-md-4">
            <input
              type="number"
              min="2000"
              max="2100"
              value={anioBoleta}
              onChange={(e) => setAnioBoleta(e.target.value)}
              className="form-control"
              placeholder="Año"
            />
          </div>
          <div className="col-md-4">
            <input
              type="number"
              min="1"
              max="12"
              value={mesBoleta}
              onChange={(e) => setMesBoleta(e.target.value)}
              className="form-control"
              placeholder="Mes"
            />
          </div>
          <div className="col-md-4">
            <button
              className="btn btn-dark"
              onClick={() => descargarPDF(`/reportes/pdf/boletas/${anioBoleta}/${mesBoleta}`, `reporte_boletas-${anioBoleta}-${mesBoleta}`)}
            >
              Descargar Boletas PDF
            </button>
          </div>
          <div className="col-md-12 mt-2">
            <p><strong>Total de Boletas:</strong> {boletasCount}</p>
          </div>
        </div>
      </div>

      {/* Facturas por Mes */}
      <div className="card mb-4">
        <div className="card-header bg-secondary text-white">Facturas por Mes</div>
        <div className="card-body row g-2">
          <div className="col-md-4">
            <input
              type="number"
              min="2000"
              max="2100"
              value={anio}
              onChange={(e) => setAnio(e.target.value)}
              className="form-control"
              placeholder="Año"
            />
          </div>
          <div className="col-md-4">
            <input
              type="number"
              min="1"
              max="12"
              value={mes}
              onChange={(e) => setMes(e.target.value)}
              className="form-control"
              placeholder="Mes"
            />
          </div>
          <div className="col-md-4">
            <button
              className="btn btn-dark"
              onClick={() => descargarPDF(`/reportes/pdf/facturas/${anio}/${mes}`, `reporte_facturas-${anio}-${mes}`)}
            >
              Descargar Facturas PDF
            </button>
          </div>
          <div className="col-md-12 mt-2">
            <p><strong>Total de Facturas:</strong> {facturasCount}</p>
          </div>
        </div>
      </div>

      {/* Reporte Stock Bajo */}
      <div className="text-center mt-4">
        <button className="btn btn-outline-secondary" onClick={() => descargarPDF('/reportes/pdf/stock-bajo', 'reporte_stock-bajo')}>
          Descargar Reporte de Stock Bajo
        </button>
      </div>
    </div>
  );
}
