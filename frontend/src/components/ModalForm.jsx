// components/ModalForm.jsx
export default function ModalForm({ title, message, onConfirm, onCancel, onType }) {
  const renderButton = () => {
    switch (onType) {
      case 'editar':
        return (
          <button type="button" className="btn btn-primary" onClick={onConfirm}>
            Confirmar
          </button>
        );
      case 'desactivar':
        return (
          <button type="button" className="btn btn-danger" onClick={onConfirm}>
            Desactivar
          </button>
        );
      case 'eliminar':
        return (
          <button type="button" className="btn btn-danger" onClick={onConfirm}>
            Eliminar
          </button>
        );
      default:
        return null;
    }
  };

  return (
    <>
      {/* Fondo oscuro manual */}
      <div className="modal-backdrop fade show"></div>

      {/* Modal centrado */}
      <div
        className="modal fade show d-block"
        tabIndex="-1"
        style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
        aria-modal="true"
        role="dialog"
      >
        <div className="modal-dialog modal-dialog-centered" role="document">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title">{title}</h5>
              <button type="button" className="btn-close" aria-label="Cerrar" onClick={onCancel}></button>
            </div>
            <div className="modal-body">
              <p>{message}</p>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" onClick={onCancel}>
                Cancelar
              </button>
              {renderButton()}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
