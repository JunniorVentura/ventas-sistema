// src/pages/Login.jsx
import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [recordarme, setRecordarme] = useState(true); //checkbox
  const [isSubmitting, setIsSubmitting] = useState(false); // nuevo estado boton login
  const [mostrarPassword, setMostrarPassword] = useState(false); // 👁

  const { login, token } = useAuth();
  const navigate = useNavigate();

  // Redirige automáticamente si ya hay sesión
  useEffect(() => {
    if (token) {
      navigate('/dashboard');
    }
  }, [token, navigate]);

  const handleLogin = async (e) => {
    e.preventDefault();
    setIsSubmitting(true); // empieza a enviar

    // Validación básica de email
    if (!/^[\w.-]+@[\w.-]+\.\w+$/.test(email)) {
      toast.warning('Correo electrónico no válido.');
      setIsSubmitting(false);
      return;
    }

    try {
      const res = await api.post('/login', { email, password });

      if (res.data?.token) {
        login(res.data.usuario, res.data.token, recordarme); // Usamos recordarme
        toast.success('¡Bienvenido!');
        navigate('/dashboard');
      } else {
        toast.error('No se recibió token. Intente nuevamente.');
      }
    } catch (err) {
      const msg = err.response?.data?.message;

      if (msg === 'Token expirado. Por favor inicie sesión nuevamente.') {
        toast.warn('Tu sesión ha expirado. Inicia sesión nuevamente.');
      } else if (msg) {
        toast.error(msg);
      } else {
        toast.error('Credenciales incorrectas o usuario inactivo.');
      }
    } finally {
      setIsSubmitting(false); // termina envío
    }
  };

  return (
    <div className="container mt-5" style={{ maxWidth: '400px' }}>
      <h3 className="mb-4">Iniciar Sesión</h3>

      <form onSubmit={handleLogin}>
        <div className="mb-3">
          <label htmlFor="email">Correo electrónico</label>
          <input
            id="email"
            type="email"
            className="form-control"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            disabled={isSubmitting}
          />
        </div>

        <div className="mb-3">
          <label htmlFor="password">Contraseña</label>
          <div className="input-group">
            <input
              id="password"
              type={mostrarPassword ? 'text' : 'password'}
              className="form-control"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              disabled={isSubmitting}
            />
            <button
              type="button"
              className="btn btn-outline-secondary"
              onClick={() => setMostrarPassword(!mostrarPassword)}
              tabIndex={-1}
            >
              <i className={`bi ${mostrarPassword ? 'bi-eye-slash' : 'bi-eye'}`}></i>
            </button>
          </div>
        </div>

        <div className="form-check mb-3">
          <input
            type="checkbox"
            className="form-check-input"
            id="recordarme"
            checked={recordarme}
            onChange={() => setRecordarme(!recordarme)}
            disabled={isSubmitting}
          />
          <label className="form-check-label" htmlFor="recordarme">
            Recordarme
          </label>
        </div>

        <button className="btn btn-primary w-100" disabled={isSubmitting}>
          {isSubmitting ? (
            <>
              <span
                className="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              Ingresando...
            </>
          ) : (
            'Ingresar'
          )}
        </button>
      </form>
    </div>
  );
}
