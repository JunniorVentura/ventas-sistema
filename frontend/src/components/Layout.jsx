// src/components/Layout.jsx
import useVerificarToken from '../hooks/useVerificarToken';
import Navbar from './Navbar';
import Sidebar from './Sidebar';
import { Outlet } from 'react-router-dom';

export default function Layout() {
  useVerificarToken();

  return (
    <>
      <Navbar />
      <div className="d-flex" style={{ minHeight: '100vh', overflow: 'hidden' }}>
        <Sidebar />
        <main className="flex-grow-1 p-4" style={{ overflowY: 'auto' }}>
          <Outlet />
        </main>
      </div>
    </>
  );
}
