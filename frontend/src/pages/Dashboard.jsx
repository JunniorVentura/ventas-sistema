import { useEffect, useState } from "react";
import api from "../services/api";
import dayjs from "dayjs";
import isBetween from "dayjs/plugin/isBetween";
import { toast } from "react-toastify";
import { Bar, Pie, Line, Doughnut } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  ArcElement,
  LineElement,
  PointElement,
  Tooltip,
  Legend,
} from 'chart.js';
import { useTienePermiso } from '../hooks/usePermisos';

dayjs.extend(isBetween);
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  ArcElement,
  LineElement,
  PointElement,
  Tooltip,
  Legend
);

export default function Dashboard() {
  const anioActual = new Date().getFullYear();
  const [anio, setAnio] = useState(anioActual);
  const [ventas, setVentas] = useState(0);
  const [pedidosMes, setPedidosMes] = useState(0);
  const [clientes, setClientes] = useState(0);
  const [pagosPendientes, setPagosPendientes] = useState(0);
  const [ultimosPedidos, setUltimosPedidos] = useState([]);
  const [chartVentas, setChartVentas] = useState(null);
  const [chartProductos, setChartProductos] = useState(null);
  const [chartPagos, setChartPagos] = useState(null);
  const [chartClientes, setChartClientes] = useState(null);
  const puedeExportar = useTienePermiso('exportar_reportes');
  const [mesSeleccionado, setMesSeleccionado] = useState("");
  const [anioProductos, setAnioProductos] = useState(anioActual);


  useEffect(() => {
    Promise.all([
      cargarDashboard(),
      cargarPagosEstado(),
      cargarClientesMensuales()
    ]).catch(() => toast.error("Error cargando el dashboard"));
  }, []);

  useEffect(() => {
    cargarVentasPorMes();
  }, [anio]);

  useEffect(() => {
    cargarProductosVendidos();
  }, [anioProductos, mesSeleccionado]);  // Ahora carga al cambiar año o mes

  const meses = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
  ];  

  const cargarDashboard = async () => {
    try {
      const hoy = dayjs();
      const inicioMes = hoy.startOf("month").format("YYYY-MM-DD");
      const finMes = hoy.endOf("month").format("YYYY-MM-DD");

      const [ventasRes, pedidosRes, clientesRes, pagosRes] = await Promise.all([
        api.get('/reportes/ventas', { params: { desde: inicioMes, hasta: finMes } }),
        api.get("/pedidos"),
        api.get("/clientes"),
        api.get("/reportes/pagos/pendiente"),
      ]);

      const totalVentas = ventasRes.data.reduce(
        (acc, pedido) => acc + (pedido.total || 0),
        0
      );

      setVentas(totalVentas);
      setPedidosMes(
        pedidosRes.data.filter(p =>
          dayjs(p.fecha_pedido).isBetween(inicioMes, finMes, null, '[]')
        ).length
      );
      setUltimosPedidos(pedidosRes.data.slice(0, 3));
      setClientes(clientesRes.data.length);
      setPagosPendientes(pagosRes.data.length);
    } catch (error) {
      console.error("Error dashboard resumen:", error);
      throw error;
    }
  };

  const cargarProductosVendidos = async () => {
    try {
      const res = await api.get('/reportes/productos-mas-vendidos', {
        params: {
          anio: anioProductos, // Usar la variable correcta
          mes: mesSeleccionado || undefined  // Solo enviar si hay mes
        }
      });
  
      if (res.data && res.data.length > 0) {
        const labels = res.data.map(item => item.nombre);
        const data = res.data.map(item => item.total_vendido || 0);
        setChartProductos({
          labels,
          datasets: [{
            label: 'Productos Vendidos',
            data,
            backgroundColor: [
              '#FF6384', '#36A2EB', '#FFCE56',
              '#4BC0C0', '#9966FF', '#FF9F40',
              '#F7464A', '#46BFBD', '#FDB45C', '#949FB1'
            ].slice(0, res.data.length),  // Colores según cantidad
          }],
        });
      } else {
        setChartProductos(null);
      }
    } catch (error) {
      console.error("Error productos más vendidos:", error);
      toast.error("Error al cargar productos más vendidos");
    }
  };

  const cargarVentasPorMes = async () => {
    try {
      const res = await api.get(`/reportes/ventas-por-mes/${anio}`);
      const meses = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
      ];
      const labels = res.data.map(item => meses[item.mes - 1]);
      const data = res.data.map(item => item.total_ventas || 0);
      setChartVentas({
        labels,
        datasets: [{
          label: `Ventas ${anio}`,
          data,
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1,
        }],
      });
    } catch (error) {
      console.error("Error ventas por mes:", error);
      toast.error("Error cargando ventas por mes");
    }
  };

  const cargarPagosEstado = async () => {
    try {
      const estados = ['pendiente', 'verificado', 'rechazado'];
      const datos = await Promise.all(estados.map(e => api.get(`/reportes/pagos/${e}`)));
      setChartPagos({
        labels: estados.map(e => e.charAt(0).toUpperCase() + e.slice(1)),
        datasets: [{
          data: datos.map(r => r.data.length),
          backgroundColor: ['#ffa500', '#4caf50', '#e74c3c'],
        }],
      });
    } catch (error) {
      console.error("Error estado de pagos:", error);
      throw error;
    }
  };

  const cargarClientesMensuales = async () => {
    try {
      const res = await api.get(`/reportes/clientes-por-mes/${anio}`);
      const meses = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
      ];
      const labels = res.data.map(item => meses[item.mes - 1]);
      const data = res.data.map(item => item.total_clientes || 0);
      setChartClientes({
        labels,
        datasets: [{
          label: `Clientes ${anio}`,
          data,
          fill: false,
          borderColor: '#FF6384',
          tension: 0.1,
        }],
      });
    } catch (error) {
      console.error("Error clientes por mes:", error);
      throw error;
    }
  };

  const aniosDisponibles = Array.from({ length: 5 }, (_, i) => anioActual - i);

  return (
    <div className="container py-4">
      <h2 className="mb-4">Dashboard</h2>
      <p className="mb-4">Bienvenido al sistema de ventas.</p>

      {/* Resumen */}
      <div className="row mb-4">
        {[
          { color: 'primary', title: 'Total Ventas', value: `S/. ${ventas}` },
          { color: 'success', title: 'Pedidos Mes', value: pedidosMes },
          { color: 'info', title: 'Clientes', value: clientes },
          { color: 'warning', title: 'Pagos Pendientes', value: pagosPendientes },
        ].map((card, i) => (
          <div className="col-md-3 mb-3" key={i}>
            <div className={`card text-white bg-${card.color} h-100`}>
              <div className="card-body">
                <h5 className="card-title">{card.title}</h5>
                <p className="card-text">{card.value}</p>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Gráficos */}
      <div className="row mb-4">
        <div className="col-lg-6 mb-3">
          <div className="card shadow">
            <div className="card-header d-flex justify-content-between align-items-center">
              <span>Ventas Mensuales</span>
              <select className="form-select w-auto" value={anio} onChange={e => setAnio(Number(e.target.value))}>
                {aniosDisponibles.map(a => (
                  <option key={a} value={a}>{a}</option>
                ))}
              </select>
            </div>
            <div className="card-body bg-light">
              {chartVentas ? <Bar data={chartVentas} /> : <p>Sin datos</p>}
            </div>
          </div>
        </div>
        <div className="col-lg-6 mb-3">
          <div className="card shadow">
            <div className="card-header d-flex justify-content-between align-items-center">
              <span>Productos Más Vendidos</span>
              <div className="d-flex gap-2">
                <select
                  className="form-select w-auto"
                  value={anioProductos}
                  onChange={e => setAnioProductos(Number(e.target.value))}
                >
                  {aniosDisponibles.map(a => (
                    <option key={a} value={a}>{a}</option>
                  ))}
                </select>
                <select
                  className="form-select w-auto"
                  value={mesSeleccionado}
                  onChange={e => setMesSeleccionado(Number(e.target.value))}
                >
                  <option value="">Todos los Meses</option>
                  {meses.map((mes, index) => (
                    <option key={index + 1} value={index + 1}>
                      {mes}
                    </option>
                  ))}
                </select>
              </div>
            </div>
            <div className="card-body bg-light">
              {chartProductos ? <Doughnut data={chartProductos} /> : <p>Sin datos</p>}
            </div>
          </div>
        </div>



        {chartPagos && (
          <div className="col-lg-6 mb-3">
            <div className="card shadow">
              <div className="card-header">Estado de Pagos</div>
              <div className="card-body bg-light">
                <Pie data={chartPagos} />
              </div>
            </div>
          </div>
        )}

        {chartClientes && (
          <div className="col-lg-6 mb-3">
            <div className="card shadow">
              <div className="card-header">Clientes por Mes</div>
              <div className="card-body bg-light">
                <Line data={chartClientes} />
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Últimos Pedidos */}
      <div className="card mb-4">
        <div className="card-header">Últimos Pedidos</div>
        <ul className="list-group list-group-flush">
          {ultimosPedidos.length > 0 ? ultimosPedidos.map(p => (
            <li key={p.id} className="list-group-item">
              Pedido #{p.id} - Cliente {p.cliente || "N/A"} - S/. {p.total}
            </li>
          )) : <li className="list-group-item">No hay pedidos recientes</li>}
        </ul>
      </div>

      {/* Accesos rápidos */}
      <div className="d-grid gap-2 d-md-flex justify-content-md-between">
        <a className="btn btn-primary" href="/ventas">Nuevo Pedido</a>
        <a className="btn btn-success" href="/pagos">Registrar Pago</a>
        <a className="btn btn-warning text-white" href="/boletas">Emitir Boletas</a>
        <a className="btn btn-warning text-white" href="/facturas">Emitir Facturas</a>
        {puedeExportar && (
          <a className="btn btn-info text-white" href="/reportes">Ver Reportes</a>
        )}
      </div>
    </div>
  );
}
