import { createContext, useContext, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import api from '../services/api';
import { eventBus } from '../utils/eventBus';

const AuthContext = createContext();

export function AuthProvider({ children }) {
  const navigate = useNavigate();

  const [usuario, setUsuario] = useState(null);
  const [token, setToken] = useState(null);
  const [permisos, setPermisos] = useState([]); // NUEVO: permisos del usuario
  const [loading, setLoading] = useState(true); // Para mostrar <Loading />

  useEffect(() => {
    const initSession = async () => {
      const storedToken = localStorage.getItem('token') || sessionStorage.getItem('token');
      const storedUser = localStorage.getItem('usuario') || sessionStorage.getItem('usuario');
  
      if (!storedToken) {
        setLoading(false);
        return;
      }
  
      try {
        api.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
  
        const { data } = await api.get('/perfil');
        //console.log("Permisos cargados desde /perfil:", data.permisos);

  
        setUsuario(data.usuario || data);
        setPermisos(data.permisos || []);
        setToken(storedToken);
  
        // Actualiza localStorage si /perfil devuelve datos nuevos
        localStorage.setItem('usuario', JSON.stringify(data.usuario || data));
      } catch (err) {
        if (storedUser) {
          setUsuario(JSON.parse(storedUser));
          setToken(storedToken);
          console.warn('Usando usuario almacenado localmente');
        } else {
          console.error('Token inválido o expirado:', err);
          toast.warn('Tu sesión ha expirado. Inicia sesión nuevamente.');
          logout(false);
        }
      } finally {
        setLoading(false);
      }
    };
  
    initSession();
    const handleLogout = () => logout();
    eventBus.addEventListener('logout', handleLogout);
  
    return () => {
      eventBus.removeEventListener('logout', handleLogout);
    };
  }, []);

  /*const login = (userData, tokenData, persist = true) => {
    const storage = persist ? localStorage : sessionStorage;

    storage.setItem('usuario', JSON.stringify(userData));
    storage.setItem('token', tokenData);

    api.defaults.headers.common['Authorization'] = `Bearer ${tokenData}`;
    setUsuario(userData);
    setToken(tokenData);
  };*/

  const login = async (userData, tokenData, persist = true) => {
    const storage = persist ? localStorage : sessionStorage;
  
    storage.setItem('usuario', JSON.stringify(userData));
    storage.setItem('token', tokenData);
  
    api.defaults.headers.common['Authorization'] = `Bearer ${tokenData}`;
    setUsuario(userData);
    setToken(tokenData);
  
    try {
      const { data } = await api.get('/perfil');
      setPermisos(data.permisos || []);
    } catch (err) {
      console.error('Error al obtener permisos en login:', err);
      toast.error('No se pudieron cargar los permisos');
    }
  };
  

  const logout = (redirigir = true) => {
    localStorage.clear();
    sessionStorage.clear();
    setUsuario(null);
    setToken(null);
    setPermisos([]);
    delete api.defaults.headers.common['Authorization'];
    if (redirigir) navigate('/');
  };

  return (
    <AuthContext.Provider value={{ usuario, token, permisos, login, logout, loading }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
