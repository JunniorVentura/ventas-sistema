// src/pages/Ventas.jsx
import React, { useEffect, useRef, useState } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';
import { Modal, Button, Form } from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';

export default function Ventas() {
  const [clientes, setClientes] = useState([]);
  const [productos, setProductos] = useState([]);
  const [categorias, setCategorias] = useState([]);
  const [categoriaId, setCategoriaId] = useState('');
  const [clienteId, setClienteId] = useState('');
  const [clienteSeleccionado, setClienteSeleccionado] = useState(null);
  const [productoId, setProductoId] = useState('');
  const [cantidad, setCantidad] = useState(1);
  const [detalle, setDetalle] = useState([]);
  const [total, setTotal] = useState(0);
  const [showConfirm, setShowConfirm] = useState(false);
  const [showClienteModal, setShowClienteModal] = useState(false);
  const [filtroCliente, setFiltroCliente] = useState('');
  const [filtroProducto, setFiltroProducto] = useState('');
  const [showNuevoClienteModal, setShowNuevoClienteModal] = useState(false);
  const [nuevoCliente, setNuevoCliente] = useState({ nombre: '', dni: '', ruc: '' });

  const { usuario } = useAuth();
  const clienteRef = useRef(null);
  const productoRef = useRef(null);

  const [loading, setLoading] = useState(false);
  const [metodoPago, setMetodoPago] = useState('efectivo');

  useEffect(() => {
    const cargarDatos = async () => {
      try {
        const [cRes, pRes, catRes] = await Promise.all([
          api.get('/clientes'),
          api.get('/productos'),
          api.get('/categorias'),
        ]);
        setClientes(cRes.data);
        setProductos(pRes.data);
        setCategorias(catRes.data);
        setTimeout(() => clienteRef.current?.focus(), 300);
      } catch {
        toast.error('Error al cargar datos');
      }
    };
    cargarDatos();
  }, []);

  useEffect(() => {
    const nuevoTotal = detalle.reduce((sum, it) => sum + it.precio_unitario * it.cantidad, 0);
    setTotal(nuevoTotal);
  }, [detalle]);

  const clientesFiltrados = clientes.filter(c =>
    c.nombre?.toLowerCase().includes(filtroCliente.trim().toLowerCase()) ||
    c.dni?.includes(filtroCliente.trim()) ||
    c.ruc?.includes(filtroCliente.trim())
  );

  const productosFiltrados = productos.filter(p =>
    (!categoriaId || p.categoria_id === Number(categoriaId)) &&
    p.nombre?.toLowerCase().includes(filtroProducto.trim().toLowerCase())
  );

  const seleccionarCliente = () => {
    const cliente = clientes.find(c => c.id === Number(clienteId));
    if (cliente) {
      setClienteSeleccionado(cliente);
      setShowClienteModal(false);
      setClienteId('');
    } else {
      toast.warning('Selecciona un cliente válido');
    }
  };

  const addProducto = () => {
    if (!productoId || cantidad < 1) {
      return toast.warn('Selecciona un producto válido y cantidad mayor a 0');
    }

    const producto = productos.find(p => p.id === Number(productoId));
    if (!producto) return toast.error('Producto no encontrado');
    if (detalle.some(p => p.producto_id === producto.id)) return toast.warning('Producto ya está en la lista');
    if (cantidad > producto.stock?.cantidad) return toast.warning('Stock insuficiente');

    setDetalle(prev => [...prev, {
      producto_id: producto.id,
      nombre: producto.nombre,
      cantidad,
      precio_unitario: parseFloat(producto.precio),
    }]);

    setProductoId('');
    setCantidad(1);
    setTimeout(() => productoRef.current?.focus(), 200);
  };

  const removeItem = (id) => {
    setDetalle(prev => prev.filter(p => p.producto_id !== id));
  };

  const confirmar = () => {
    if (!clienteSeleccionado) return toast.warning('Selecciona un cliente');
    if (detalle.length === 0) return toast.warning('Agrega al menos un producto');
    setShowConfirm(true);
  };

  // Función para registrar pedido con pago
  const enviarConPago = async () => {
    setLoading(true);
    try {
      const pedidoRes = await api.post('/pedidos', {
        cliente_id: clienteSeleccionado.id,
        usuario_id: usuario.id,
        total,
        estado_pedido:'pagado',
      });

      const pedidoId = pedidoRes.data.id;

      // Registrar detalles del pedido
      await Promise.all(detalle.map(item =>
        api.post('/detalle-pedidos', {
          pedido_id: pedidoId,
          producto_id: item.producto_id,
          cantidad: item.cantidad,
          precio_unitario: item.precio_unitario,
        })
      ));

      // Registrar pago
      await api.post('/pagos', {
        pedido_id: pedidoId,
        metodo_pago: metodoPago,
      });

      toast.success('Venta registrada y pagada correctamente');
      setClienteSeleccionado(null);
      setDetalle([]);
      setShowConfirm(false);
    } catch (error) {
      console.error(error);
      toast.error('Error al registrar la venta con pago');
    } finally {
      setLoading(false);
    }
  };

  // Función para registrar pedido sin pago
  const enviarSinPago = async () => {
    setLoading(true);
    try {
      const pedidoRes = await api.post('/pedidos', {
        cliente_id: clienteSeleccionado.id,
        usuario_id: usuario.id,
        total,
        estado_pedido: 'pendiente',  // Para dejarlo pendiente
      });

      const pedidoId = pedidoRes.data.id;

      // Registrar detalles del pedido
      await Promise.all(detalle.map(item =>
        api.post('/detalle-pedidos', {
          pedido_id: pedidoId,
          producto_id: item.producto_id,
          cantidad: item.cantidad,
          precio_unitario: item.precio_unitario,
        })
      ));

      toast.success('Venta registrada sin pago (pendiente)');
      setClienteSeleccionado(null);
      setDetalle([]);
      setShowConfirm(false);
    } catch (error) {
      console.error(error);
      toast.error('Error al registrar la venta sin pago');
    } finally {
      setLoading(false);
    }
  };
    
  const registrarNuevoCliente = async () => {
    const { nombre, email } = nuevoCliente;
    if (!nombre.trim()) {
      return toast.warning('El nombre del cliente es obligatorio');
    }
    if (email && !/\S+@\S+\.\S+/.test(email)) {
      return toast.warning('El email no es válido');
    }

    try {
      const res = await api.post('/clientes', nuevoCliente);
      setClientes(prev => [...prev, res.data]);
      toast.success('Cliente registrado');
      setNuevoCliente({
        nombre: '',
        dni: '',
        ruc: '',
        razon_social: '',
        direccion: '',
        telefono: '',
        email: ''
      });
      setShowNuevoClienteModal(false);
    } catch {
      toast.error('Error al registrar nuevo cliente');
    }
  };


  return (
    <div className="container mt-4">
      <h3 className="mb-4">Registrar Venta</h3>

      {/* Selección Cliente */}
      {!clienteSeleccionado && (
        <>
          <div className="mb-3 d-flex">
            <input
              type="text"
              className="form-control me-2"
              placeholder="Buscar cliente por nombre, DNI o RUC"
              value={filtroCliente}
              onChange={e => setFiltroCliente(e.target.value)}
              ref={clienteRef}
            />
            <button className="btn btn-secondary me-2" onClick={() => setShowClienteModal(true)}>Seleccionar</button>
            <button className="btn btn-outline-primary" onClick={() => setShowNuevoClienteModal(true)}>+ Añadir Cliente</button>
          </div>

          {/* Modal Seleccionar Cliente */}
          <Modal show={showClienteModal} onHide={() => setShowClienteModal(false)}>
            <Modal.Header closeButton><Modal.Title>Seleccionar Cliente</Modal.Title></Modal.Header>
            <Modal.Body>
              <select className="form-select" value={clienteId} onChange={e => setClienteId(e.target.value)}>
                <option value="">-- Selecciona Cliente --</option>
                {clientesFiltrados.map(c => (
                  <option key={c.id} value={c.id}>{c.nombre} - {c.dni || c.ruc}</option>
                ))}
              </select>
            </Modal.Body>
            <Modal.Footer>
              <Button variant="secondary" onClick={() => setShowClienteModal(false)}>Cancelar</Button>
              <Button variant="primary" onClick={seleccionarCliente}>Aceptar</Button>
            </Modal.Footer>
          </Modal>

          {/* Modal Nuevo Cliente */}
          <Modal show={showNuevoClienteModal} onHide={() => setShowNuevoClienteModal(false)}>
            <Modal.Header closeButton>
              <Modal.Title>Nuevo Cliente</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <Form.Group className="mb-3">
                <Form.Label>Nombre <span className="text-danger">*</span></Form.Label>
                <Form.Control
                  value={nuevoCliente.nombre}
                  onChange={e => setNuevoCliente({ ...nuevoCliente, nombre: e.target.value })}
                  required
                />
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>DNI</Form.Label>
                <Form.Control
                  value={nuevoCliente.dni}
                  onChange={e => setNuevoCliente({ ...nuevoCliente, dni: e.target.value })}
                />
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>RUC</Form.Label>
                <Form.Control
                  value={nuevoCliente.ruc}
                  onChange={e => setNuevoCliente({ ...nuevoCliente, ruc: e.target.value })}
                />
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>Razón Social</Form.Label>
                <Form.Control
                  value={nuevoCliente.razon_social}
                  onChange={e => setNuevoCliente({ ...nuevoCliente, razon_social: e.target.value })}
                />
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>Dirección</Form.Label>
                <Form.Control
                  value={nuevoCliente.direccion}
                  onChange={e => setNuevoCliente({ ...nuevoCliente, direccion: e.target.value })}
                />
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>Teléfono</Form.Label>
                <Form.Control
                  value={nuevoCliente.telefono}
                  onChange={e => setNuevoCliente({ ...nuevoCliente, telefono: e.target.value })}
                />
              </Form.Group>
              <Form.Group>
                <Form.Label>Email</Form.Label>
                <Form.Control
                  type="email"
                  value={nuevoCliente.email}
                  onChange={e => setNuevoCliente({ ...nuevoCliente, email: e.target.value })}
                />
              </Form.Group>
            </Modal.Body>
            <Modal.Footer>
              <Button variant="secondary" onClick={() => setShowNuevoClienteModal(false)}>Cancelar</Button>
              <Button variant="success" onClick={registrarNuevoCliente}>Registrar</Button>
            </Modal.Footer>
          </Modal>

        </>
      )}

      {/* Cliente Seleccionado */}
      {clienteSeleccionado && (
        <div className="alert alert-info d-flex justify-content-between align-items-center">
          <div>
            <strong>Cliente:</strong> {clienteSeleccionado.nombre}<br />
            <strong>DNI:</strong> {clienteSeleccionado.dni || '-'}<br />
            <strong>RUC:</strong> {clienteSeleccionado.ruc || '-'}<br />
            <strong>Razón Social:</strong> {clienteSeleccionado.razon_social || '-'}
          </div>
          <button className="btn btn-warning" onClick={() => {
            setClienteSeleccionado(null);
            setDetalle([]);
          }}>
            Cambiar Cliente
          </button>
        </div>
      )}

      {/* Filtros y Producto */}
      <div className="row mb-3">
        <div className="col-sm-3">
          <select className="form-select" value={categoriaId} onChange={e => setCategoriaId(e.target.value)}>
            <option value="">-- Todas las Categorías --</option>
            {categorias.map(c => (
              <option key={c.id} value={c.id}>{c.nombre}</option>
            ))}
          </select>
        </div>
        <div className="col-sm-5">
          <input type="text" className="form-control" placeholder="Buscar producto por nombre" value={filtroProducto} onChange={e => setFiltroProducto(e.target.value)} />
        </div>
        <div className="col-sm-4">
          <select className="form-select" value={productoId} onChange={e => setProductoId(e.target.value)} ref={productoRef}>
            <option value="">-- Selecciona Producto --</option>
            {productosFiltrados.map(p => (
              <option key={p.id} value={p.id}>
                {p.nombre} (Stock: {p.stock?.cantidad}) - S/.{parseFloat(p.precio).toFixed(2)}
              </option>
            ))}
          </select>
        </div>
      </div>

      <div className="row mb-3">
        <div className="col-sm-2">
          <input type="number" min="1" className="form-control" value={cantidad} onChange={e => setCantidad(Number(e.target.value))} />
        </div>
        <div className="col-sm-2">
          <button className="btn btn-primary w-100" onClick={addProducto}>Añadir</button>
        </div>
      </div>

      {/* Tabla Detalle */}
      <table className="table table-striped">
        <thead>
          <tr>
            <th>Producto</th><th>Cant.</th><th>Precio U.</th><th>Subtotal</th><th></th>
          </tr>
        </thead>
        <tbody>
          {detalle.map(it => (
            <tr key={it.producto_id}>
              <td>{it.nombre}</td>
              <td>{it.cantidad}</td>
              <td>S/.{it.precio_unitario.toFixed(2)}</td>
              <td>S/.{(it.precio_unitario * it.cantidad).toFixed(2)}</td>
              <td><button className="btn btn-danger btn-sm" onClick={() => removeItem(it.producto_id)}>✕</button></td>
            </tr>
          ))}
          {detalle.length === 0 && <tr><td colSpan="5" className="text-center">Sin productos</td></tr>}
        </tbody>
      </table>

      {/* Total y Registro de Venta */}
      <div className="mb-4">
        <h5>Total: S/.{total.toFixed(2)}</h5>

        {/* Botón Registrar sin pago */}
        <button
          className="btn btn-warning me-2"
          disabled={!clienteSeleccionado || detalle.length === 0 || loading}
          onClick={enviarSinPago}
        >
          Registrar Venta sin Pago
        </button>

        {/* Selector método de pago */}
        <div className="my-3">
          <Form.Label>Método de Pago</Form.Label>
          <Form.Select
            value={metodoPago}
            onChange={(e) => setMetodoPago(e.target.value)}
            disabled={!clienteSeleccionado || detalle.length === 0}
          >
            <option value="">-- Seleccionar Método de Pago --</option>
            <option value="efectivo">Efectivo</option>
            <option value="yape">Yape</option>
            <option value="transferencia">Transferencia</option>
          </Form.Select>
        </div>

        {/* Botón Registrar con pago */}
        <button
          className="btn btn-primary"
          disabled={!clienteSeleccionado || detalle.length === 0 || !metodoPago || loading}
          onClick={enviarConPago}
        >
          Registrar Venta con Pago
        </button>
      </div>

    </div>
  );
}
