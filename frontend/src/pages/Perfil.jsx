import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';
import { toast } from 'react-toastify';

export default function Perfil() {
  const { usuario, token, login } = useAuth();

  const [form, setForm] = useState({
    nombre: usuario?.nombre || '',
    email: usuario?.email || '',
    current_password: '',
  });

  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false); // Mostrar/Ocultar contraseña

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!form.current_password) {
      return toast.warning('Debes ingresar tu contraseña actual para actualizar tu perfil.');
    }

    setLoading(true);

    try {
      const res = await api.put(`/usuarios/${usuario.id}`, form);
      login(res.data, token);
      toast.success('Perfil actualizado correctamente.');
      setForm((prev) => ({
        ...prev,
        current_password: '',
      }));
    } catch (err) {
      if (err.response?.data?.errors) {
        const errores = Object.values(err.response.data.errors).flat();
        errores.forEach((msg) => toast.error(msg));
      } else {
        toast.error('Error al actualizar el perfil.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container mt-4" style={{ maxWidth: '600px' }}>
      <h3 className="mb-4">Mi Perfil</h3>

      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label htmlFor="nombre" className="form-label">
            Nombre
          </label>
          <input
            type="text"
            className="form-control"
            id="nombre"
            name="nombre"
            value={form.nombre}
            onChange={handleChange}
            required
          />
        </div>

        <div className="mb-3">
          <label htmlFor="email" className="form-label">
            Correo electrónico
          </label>
          <input
            type="email"
            className="form-control"
            id="email"
            name="email"
            value={form.email}
            onChange={handleChange}
            required
          />
        </div>

        <div className="mb-3">
          <label htmlFor="current_password" className="form-label">
            Contraseña actual
          </label>
          <div className="input-group">
            <input
              type={showPassword ? 'text' : 'password'}
              className="form-control"
              id="current_password"
              name="current_password"
              value={form.current_password}
              onChange={handleChange}
              required
            />
            <button
              type="button"
              className="btn btn-outline-secondary"
              onClick={() => setShowPassword((prev) => !prev)}
              tabIndex={-1}
            >
              <i className={`bi ${showPassword ? 'bi-eye-slash' : 'bi-eye'}`}></i>
            </button>
          </div>
        </div>

        <button type="submit" className="btn btn-primary w-100" disabled={loading}>
          {loading ? 'Guardando...' : 'Guardar Cambios'}
        </button>
      </form>
    </div>
  );
}
