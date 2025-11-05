// src/components/Loading.jsx
import { useEffect, useState } from 'react';

export default function Loading({ delay = 300, fullscreen = true }) {
  const [show, setShow] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => setShow(true), delay);
    return () => clearTimeout(timer);
  }, [delay]);

  if (!show) return null;

  return (
    <div
      className={`${
        fullscreen ? 'position-fixed' : 'position-absolute'
      } top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center`}
      style={{ zIndex: 1050 }}
    >
      <div className="text-center text-white" role="status" aria-live="polite">
        {/* Logo opcional */}
        {/* <img src="/logo.png" alt="Cargando..." style={{ width: '80px' }} className="mb-3" /> */}

        <div
          className="spinner-border text-light mb-3"
          style={{ width: '3rem', height: '3rem' }}
        />
        <div className="fw-semibold fs-5">Cargando...</div>
      </div>
    </div>
  );
}
