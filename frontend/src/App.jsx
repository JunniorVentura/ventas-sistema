// src/App.jsx
import RouterApp from './routes/Routes';
import Loading from './components/Loading';
import { useAuth } from './context/AuthContext';

export default function App() {
  const { loading } = useAuth();

  if (loading) {
    return <Loading delay={300} />; // opcionalmente puedes cambiar el delay (ms), fullscreen = true, por defecto
  }

  return <RouterApp />;
}
