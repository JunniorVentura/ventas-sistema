import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';
import { toast } from 'react-toastify';

export default function CambiarPassword() {
  const { usuario, logout } = useAuth();

  const [form, setForm] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState({
    current: false,
    new: false,
    confirm: false,
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const toggleVisibility = (field) => {
    setShowPassword((prev) => ({ ...prev, [field]: !prev[field] }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (form.password !== form.password_confirmation) {
      return toast.warning('Las contraseñas no coinciden');
    }

    if (!form.current_password) {
      return toast.warning('Debes ingresar tu contraseña actual');
    }

    setLoading(true);

    try {
      await api.put(`/usuarios/${usuario.id}/password`, {
        current_password: form.current_password,
        password: form.password,
        password_confirmation: form.password_confirmation,
      });

      toast.success('Contraseña actualizada. Inicia sesión nuevamente.');

      setTimeout(() => {
        logout();
        window.location.href = '/login';
      }, 1500);
    } catch (err) {
      if (err.response?.data?.errors) {
        const errores = Object.values(err.response.data.errors).flat();
        errores.forEach((msg) => toast.error(msg));
      } else {
        toast.error('Error al actualizar la contraseña.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container mt-4" style={{ maxWidth: '600px' }}>
      <h3 className="mb-4">Cambiar Contraseña</h3>

      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label htmlFor="current_password" className="form-label">
            Contraseña actual
          </label>
          <div className="input-group">
            <input
              type={showPassword.current ? 'text' : 'password'}
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
              onClick={() => toggleVisibility('current')}
              tabIndex={-1}
            >
              <i className={`bi ${showPassword.current ? 'bi-eye-slash' : 'bi-eye'}`}></i>
            </button>
          </div>
        </div>

        <div className="mb-3">
          <label htmlFor="password" className="form-label">
            Nueva contraseña
          </label>
          <div className="input-group">
            <input
              type={showPassword.new ? 'text' : 'password'}
              className="form-control"
              id="password"
              name="password"
              value={form.password}
              onChange={handleChange}
              required
            />
            <button
              type="button"
              className="btn btn-outline-secondary"
              onClick={() => toggleVisibility('new')}
              tabIndex={-1}
            >
              <i className={`bi ${showPassword.new ? 'bi-eye-slash' : 'bi-eye'}`}></i>
            </button>
          </div>
        </div>

        <div className="mb-3">
          <label htmlFor="password_confirmation" className="form-label">
            Confirmar nueva contraseña
          </label>
          <div className="input-group">
            <input
              type={showPassword.confirm ? 'text' : 'password'}
              className="form-control"
              id="password_confirmation"
              name="password_confirmation"
              value={form.password_confirmation}
              onChange={handleChange}
              required
            />
            <button
              type="button"
              className="btn btn-outline-secondary"
              onClick={() => toggleVisibility('confirm')}
              tabIndex={-1}
            >
              <i className={`bi ${showPassword.confirm ? 'bi-eye-slash' : 'bi-eye'}`}></i>
            </button>
          </div>
        </div>

        <button type="submit" className="btn btn-primary w-100" disabled={loading}>
          {loading ? 'Cambiando...' : 'Cambiar Contraseña'}
        </button>
      </form>
    </div>
  );
}
