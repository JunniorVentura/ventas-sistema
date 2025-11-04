// src/pages/Clientes.jsx
import { useEffect, useRef, useState } from "react";
import api from "../services/api";
import { toast } from "react-toastify";
import ModalForm from '../components/ModalForm';

import { useTienePermiso } from "../hooks/usePermisos";

export default function Clientes() {
  const puedeVer = useTienePermiso("ver_clientes");
  const puedeCrear = useTienePermiso("crear_clientes");
  const puedeEditar = useTienePermiso("editar_clientes");
  const puedeEliminar = useTienePermiso("eliminar_clientes");

  const [clientes, setClientes] = useState([]);
  const [editando, setEditando] = useState(null);
  const [formData, setFormData] = useState({
    nombre: "",
    dni: "",
    ruc: "",
    direccion: "",
    telefono: "",
    email: "",
  });

  const [busqueda, setBusqueda] = useState("");
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const [clienteAEditar, setClienteAEditar] = useState(null);
  const [showModalEditar, setShowModalEditar] = useState(false);
  const [showModalEliminar, setShowModalEliminar] = useState(false);
  const [clienteAEliminar, setClienteAEliminar] = useState(null);
  const inputNombreRef = useRef(null);

  const [guardando, setGuardando] = useState(false); //estadoSubmit

  useEffect(() => {
    if (puedeVer) obtenerClientes();
  }, [puedeVer]);

  const obtenerClientes = async () => {
    try {
      const res = await api.get("/clientes");
      setClientes(res.data);
    } catch {
      toast.error("Error al obtener los clientes");
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((f) => ({ ...f, [name]: value }));
  };

  const limpiarFormulario = () => {
    setFormData({
      nombre: "",
      dni: "",
      ruc: "",
      razon_social: "",
      direccion: "",
      telefono: "",
      email: "",
    });
    setEditando(null);
    setTimeout(() => inputNombreRef.current?.focus(), 200);
  };

  const validarFormulario = () => {
    if (!formData.nombre.trim()) {
      toast.warning("El nombre es obligatorio");
      inputNombreRef.current?.focus();
      return false;
    }
    if (formData.email && !/\S+@\S+\.\S+/.test(formData.email)) {
      toast.warning("El email no es válido");
      return false;
    }
    return true;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validarFormulario()) return;

    if (editando && !puedeEditar) return toast.error("No tienes permiso para editar clientes.");
    if (!editando && !puedeCrear) return toast.error("No tienes permiso para crear clientes.");

    setGuardando(true);
    try {
      if (editando) {
        await api.put(`/clientes/${editando}`, formData);
        toast.success("Cliente actualizado");
      } else {
        await api.post("/clientes", formData);
        toast.success("Cliente creado");
      }
      limpiarFormulario();
      obtenerClientes();
    } catch {
      toast.error("Error al guardar el cliente");
    } finally {
      setGuardando(false);
    }
    
  };

  const abrirModalEditar = (cliente) => {
    if (showModalEditar) return; // Previene abrir varios modales accidentalmente
    if (!puedeEditar) return toast.error("No tienes permiso para editar clientes.");
    setClienteAEditar(cliente);
    setShowModalEditar(true);
  };

  const confirmarEditar = () => {
    setEditando(clienteAEditar.id);
    setFormData({
      nombre: clienteAEditar.nombre || "",
      dni: clienteAEditar.dni || "",
      ruc: clienteAEditar.ruc || "",
      razon_social: clienteAEditar.razon_social || "",
      direccion: clienteAEditar.direccion || "",
      telefono: clienteAEditar.telefono || "",
      email: clienteAEditar.email || "",
    });
    setShowModalEditar(false);
    window.scrollTo({ top: 0, behavior: "smooth" });
    setTimeout(() => inputNombreRef.current?.focus(), 300);
  };

  const abrirModalEliminar = (cliente) => {
    if (showModalEliminar) return; // Previene abrir varios modales accidentalmente
    if (!puedeEliminar) return toast.error("No tienes permiso para eliminar clientes.");
    setClienteAEliminar(cliente);
    setShowModalEliminar(true);
  };

  const confirmarEliminar = async () => {
    if (!puedeEliminar) return toast.error("No tienes permiso para eliminar clientes.");
    try {
      await api.delete(`/clientes/${clienteAEliminar.id}`);
      toast.success("Cliente desactivado");
      obtenerClientes();
    } catch {
      toast.error("Error al desactivar cliente");
    }
    setShowModalEliminar(false);
    setClienteAEliminar(null);
  };

  const clientesFiltrados = clientes.filter((c) =>
    c.nombre.toLowerCase().includes(busqueda.toLowerCase())
  );

  const totalPaginas = Math.ceil(clientesFiltrados.length / porPagina);
  const clientesPaginados = clientesFiltrados.slice(
    (pagina - 1) * porPagina,
    pagina * porPagina
  );

  if (!puedeVer) {
    return <div className="container mt-4"><p>No tienes permiso para ver esta sección.</p></div>;
  }

  return (
    <div className="container mt-4">
      <h4 className="mb-4 text-primary fw-bold">
        {editando ? "Editar Cliente" : "Registrar Nuevo Cliente"}
      </h4>

      {(puedeCrear || (editando && puedeEditar)) && (
        <form onSubmit={handleSubmit} className="mb-4">
          <div className="row g-3">
            <div className="col-md-12">
              <label className="form-label">Nombre <span className="text-danger">*</span></label>
              <input
                name="nombre"
                type="text"
                className="form-control"
                value={formData.nombre}
                onChange={handleChange}
                ref={inputNombreRef}
                required
              />
            </div>
            <div className="col-md-3">
              <label className="form-label">DNI</label>
              <input name="dni" type="text" className="form-control" value={formData.dni} onChange={handleChange} maxLength={15} />
            </div>
            <div className="col-md-3">
              <label className="form-label">RUC</label>
              <input name="ruc" type="text" className="form-control" value={formData.ruc} onChange={handleChange} maxLength={15} />
            </div>
            <div className="col-md-6">
              <label className="form-label">Razón Social</label>
              <input name="razon_social" type="text" className="form-control" value={formData.razon_social} onChange={handleChange} maxLength={150}/>
            </div>
            <div className="col-md-6">
              <label className="form-label">Dirección</label>
              <input name="direccion" type="text" className="form-control" value={formData.direccion} onChange={handleChange} />
            </div>
            <div className="col-md-3">
              <label className="form-label">Teléfono</label>
              <input name="telefono" type="text" className="form-control" value={formData.telefono} onChange={handleChange} maxLength={20} />
            </div>
            <div className="col-md-3">
              <label className="form-label">Email</label>
              <input name="email" type="email" className="form-control" value={formData.email} onChange={handleChange} maxLength={100} />
            </div>
            <div className="col-12 d-flex gap-2">
            <button type="submit" className="btn btn-primary" disabled={guardando}>
              {editando ? "Actualizar" : "Registrar"}
            </button>
              {editando && (
                <button type="button" className="btn btn-secondary" onClick={limpiarFormulario}>
                  Cancelar
                </button>
              )}
            </div>
          </div>
        </form>
      )}
      {/* Búsqueda */}
      <div className="mb-3">
        <input
          type="text"
          className="form-control"
          placeholder="Buscar cliente por nombre..."
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
      <div className="table-responsive">
        <table className="table table-bordered table-hover align-middle">
          <thead className="table-dark">
            <tr>
              <th>Nombre</th>
              <th>DNI</th>
              <th>RUC</th>
              <th>Dirección</th>
              <th>Teléfono</th>
              <th>Email</th>
              {(puedeEditar || puedeEliminar) && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {clientesPaginados.length > 0 ? (
              clientesPaginados.map((cliente) => (
                <tr key={cliente.id}>
                  <td>{cliente.nombre}</td>
                  <td>{cliente.dni || "-"}</td>
                  <td>{cliente.ruc || "-"}</td>
                  <td>{cliente.direccion || "-"}</td>
                  <td>{cliente.telefono || "-"}</td>
                  <td>{cliente.email || "-"}</td>
                  {(puedeEditar || puedeEliminar) && (
                    <td>
                      {puedeEditar && (
                        <button className="btn btn-sm btn-warning me-2" onClick={() => abrirModalEditar(cliente)}>Editar</button>
                      )}
                      {puedeEliminar && (
                        <button className="btn btn-sm btn-danger" onClick={() => abrirModalEliminar(cliente)}>Eliminar</button>
                      )}
                    </td>
                  )}
                </tr>
              ))
            ) : (
              <tr><td colSpan={puedeEditar || puedeEliminar ? 7 : 6} className="text-center">No hay resultados.</td></tr>
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
          message={`¿Deseas editar al cliente ${clienteAEditar?.nombre}?`}
          onCancel={() => setShowModalEditar(false)}
          onConfirm={confirmarEditar}
          onType='editar'
        />
      )}
      {showModalEliminar && (
        <ModalForm
          title="Confirmar Desactivación"
          message={`¿Deseas desactivar al cliente ${clienteAEliminar?.nombre}?`}
          onCancel={() => setShowModalEliminar(false)}
          onConfirm={confirmarEliminar}
          onType='desactivar'
        />
      )}
    </div>
  );
}
