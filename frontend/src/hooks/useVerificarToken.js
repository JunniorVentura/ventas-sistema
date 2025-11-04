import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';
import { toast } from 'react-toastify';

export default function useVerificarToken() {
  const navigate = useNavigate();
  const { logout } = useAuth();

  useEffect(() => {
    const verificarToken = async () => {
      const token = localStorage.getItem('token');
      if (!token) {
        logout();
        return;
      }

      try {
        await api.get('/perfil'); // O cualquier endpoint seguro
      } catch (error) {
        if (error.response?.status === 401) {
          toast.warning('Tu sesión ha expirado o no es válida.');
          logout();
        }
      }
    };

    verificarToken();
  }, [logout, navigate]);
}
