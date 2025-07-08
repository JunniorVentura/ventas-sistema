import { useEffect, useRef, useState } from "react";
import api from "../services/api";
import { toast } from "react-toastify";
import ModalForm from '../components/ModalForm';

import { useTienePermiso } from "../hooks/usePermisos";

export default function Productos() {
  const puedeVer = useTienePermiso("ver_productos");
  const puedeCrear = useTienePermiso("crear_productos");
  const puedeEditar = useTienePermiso("editar_productos");
  const puedeEliminar = useTienePermiso("eliminar_productos");

  const [productos, setProductos] = useState([]);
  const [categorias, setCategorias] = useState([]);
  const [editando, setEditando] = useState(null);
  const [formData, setFormData] = useState({
    nombre: "",
    descripcion: "",
    precio: "",
    categoria_id: "",
  });

  const [busqueda, setBusqueda] = useState("");
  const [pagina, setPagina] = useState(1);
  const [porPagina, setPorPagina] = useState(5);

  const [prodAEliminar, setProdAEliminar] = useState(null);
  const [prodAEditar, setProdAEditar] = useState(null);
  const [showModalEliminar, setShowModalEliminar] = useState(false);
  const [showModalEditar, setShowModalEditar] = useState(false);
  const inputNameRef = useRef(null);
  const [guardando, setGuardando] = useState(false); //estadoSubmit


  const cargarDatos = async () => {
    try {
      const [resProd, resCat] = await Promise.all([
        api.get("/productos"),
        api.get("/categorias"),
      ]);
      setProductos(resProd.data);
      setCategorias(resCat.data);
    } catch {
      toast.error("Error al cargar productos o categorías");
    }
  };

  useEffect(() => {
    if (puedeVer) {
      cargarDatos();
    }
  }, [puedeVer]);

  const limpiarFormulario = () => {
    setFormData({ nombre: "", descripcion: "", precio: "", categoria_id: "" });
    setEditando(null);
    setTimeout(() => inputNameRef.current?.focus(), 200);
  };

  const validar = () => {
    if (!formData.nombre.trim()) {
      toast.warning("El nombre es obligatorio");
      inputNameRef.current?.focus();
      return false;
    }
    if (!formData.precio || Number(formData.precio) < 0) {
      toast.warning("El precio debe ser un número no negativo");
      return false;
    }
    if (!formData.categoria_id) {
      toast.warning("Selecciona una categoría");
      return false;
    }
    return true;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (editando && !puedeEditar) {
      return toast.error("No tienes permiso para editar productos");
    }
    if (!editando && !puedeCrear) {
      return toast.error("No tienes permiso para crear productos");
    }
    if (!validar()) return;

    setGuardando(true);
    try {
      if (editando) {
        await api.put(`/productos/${editando}`, formData);
        toast.success("Producto actualizado");
      } else {
        await api.post("/productos", formData);
        toast.success("Producto creado");
      }
      limpiarFormulario();
      cargarDatos();
    } catch {
      toast.error("Error al guardar el producto");
    } finally {
      setGuardando(false);
    }
  };

  const abrirModalEditar = (prod) => {
    if (!puedeEditar) return toast.error("No tienes permiso para editar");
    setProdAEditar(prod);
    setShowModalEditar(true);
  };

  const confirmarEditar = () => {
    setEditando(prodAEditar.id);
    setFormData({
      nombre: prodAEditar.nombre,
      descripcion: prodAEditar.descripcion || "",
      precio: prodAEditar.precio,
      categoria_id: prodAEditar.categoria_id,
    });
    setShowModalEditar(false);
    window.scrollTo({ top: 0, behavior: "smooth" });
    setTimeout(() => inputNameRef.current?.focus(), 300);
  };

  const abrirModalEliminar = (prod) => {
    if (!puedeEliminar) return toast.error("No tienes permiso para eliminar");
    setProdAEliminar(prod);
    setShowModalEliminar(true);
  };

  const confirmarEliminar = async () => {
    if (!puedeEliminar) return toast.error("No tienes permiso para eliminar");
    try {
      await api.delete(`/productos/${prodAEliminar.id}`);
      toast.success("Producto desactivado");
      cargarDatos();
    } catch {
      toast.error("Error al eliminar producto");
    }
    setShowModalEliminar(false);
  };

  const productosFiltrados = productos.filter((p) =>
    p.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
    (p.descripcion || "").toLowerCase().includes(busqueda.toLowerCase())
  );
  const totalPaginas = Math.ceil(productosFiltrados.length / porPagina);
  const productosPaginados = productosFiltrados.slice(
    (pagina - 1) * porPagina,
    pagina * porPagina
  );

  if (!puedeVer) {
    return <div className="container mt-4"><p>No tienes permiso para ver esta sección.</p></div>;
  }

  return (
    <div className="container mt-4">
      <h4 className="mb-4 text-primary fw-bold">
        {editando ? "Editar Producto" : "Registrar Nuevo Producto"}
      </h4>

      {(puedeCrear || puedeEditar) && (
        <form onSubmit={handleSubmit} className="mb-4">
          <div className="row g-3">
            <div className="col-md-4">
              <label className="form-label">Nombre<span className="text-danger">*</span></label>
              <input
                ref={inputNameRef}
                type="text"
                className="form-control"
                name="nombre"
                value={formData.nombre}
                onChange={(e) => setFormData({ ...formData, nombre: e.target.value })}
                required
              />
            </div>
            <div className="col-md-4">
              <label className="form-label">Precio<span className="text-danger">*</span></label>
              <input
                type="number"
                className="form-control"
                name="precio"
                value={formData.precio}
                min="0"
                step="0.01"
                onChange={(e) => setFormData({ ...formData, precio: e.target.value })}
                required
              />
            </div>
            <div className="col-md-4">
              <label className="form-label">Categoría<span className="text-danger">*</span></label>
              <select
                className="form-select"
                name="categoria_id"
                value={formData.categoria_id}
                onChange={(e) => setFormData({ ...formData, categoria_id: e.target.value })}
                required
              >
                <option value="">-- Selecciona --</option>
                {categorias.map((c) => (
                  <option key={c.id} value={c.id}>{c.nombre}</option>
                ))}
              </select>
            </div>
            <div className="col-md-12">
              <label className="form-label">Descripción</label>
              <textarea
                className="form-control"
                name="descripcion"
                value={formData.descripcion}
                onChange={(e) => setFormData({ ...formData, descripcion: e.target.value })}
              />
            </div>
            <div className="col-12 d-flex gap-2">
              <button type="submit" className="btn btn-primary" disabled={guardando}>
                {editando ? "Actualizar" : "Registrar"}
              </button>
              {editando &&
                <button type="button" className="btn btn-secondary" onClick={limpiarFormulario}>
                  Cancelar
                </button>
              }
            </div>
          </div>
        </form>
      )}

      {/* Buscador */}
      <div className="mb-3">
        <input
          className="form-control"
          placeholder="Buscar producto..."
          value={busqueda}
          onChange={(e) => { setBusqueda(e.target.value); setPagina(1); }}
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
          <thead className="table-dark">
            <tr><th>Nombre</th><th>Precio</th><th>Categoría</th><th>Stock</th>{(puedeEditar || puedeEliminar) && <th>Acciones</th>}</tr>
          </thead>
          <tbody>
            {productosPaginados.length ? productosPaginados.map((prod) => (
              <tr key={prod.id}>
                <td>{prod.nombre}</td>
                <td>{!isNaN(prod.precio) ? Number(prod.precio).toFixed(2) : '0.00'}</td>
                <td>{prod.categoria?.nombre || "-"}</td>
                <td>{prod.stock?.cantidad || 0}</td>
                {(puedeEditar || puedeEliminar) && (
                  <td>
                    {puedeEditar && (
                      <button className="btn btn-sm btn-warning me-2" onClick={() => abrirModalEditar(prod)}>Editar</button>
                    )}
                    {puedeEliminar && (
                      <button className="btn btn-sm btn-danger" onClick={() => abrirModalEliminar(prod)}>Eliminar</button>
                    )}
                  </td>
                )}
              </tr>
            )) : (
              <tr><td colSpan={puedeEditar || puedeEliminar ? 4 : 3} className="text-center">No hay resultados</td></tr>
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
          message={`¿Deseas editar el producto ${prodAEditar?.nombre}?`}
          onCancel={() => setShowModalEditar(false)}
          onConfirm={confirmarEditar}
          onType="editar"
        />
      )}
      {showModalEliminar && (
        <ModalForm
          title="Confirmar Eliminación"
          message={`¿Deseas eliminar el producto ${prodAEliminar?.nombre}?`}
          onCancel={() => setShowModalEliminar(false)}
          onConfirm={confirmarEliminar}
          onType="eliminar"
        />
      )}
    </div>
  );
}
