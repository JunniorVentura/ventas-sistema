<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// IMPORTAR CONTROLADORES API
use App\Http\Controllers\Api\{
    RolController,
    PermisoController,
    RolPermisoController,
    UsuarioController,
    ClienteController,
    CategoriaController,
    ProductoController,
    StockController,
    PedidoController,
    DetallePedidoController,
    FacturaController,
    DetalleFacturaController,
    BoletaController,
    DetalleBoletaController,
    PagoController,
    LogController,
    ReporteController,
    ReportePdfController
};

// Ruta protegida para prueba de login
Route::middleware(['auth:sanctum'])->get('/protegido', fn(Request $request) => response('OK'));

// RUTAS PÚBLICAS
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// RUTAS PROTEGIDAS
Route::middleware(['auth:sanctum'])->group(function () {

    // PERFIL Y CIERRE DE SESIÓN
    Route::get('/perfil', [AuthController::class, 'perfil']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // LOGS
    Route::middleware('permiso:ver_logs')->group(function () {
        Route::get('logs', [LogController::class, 'index']);
        Route::get('logs/usuario/{id}', [LogController::class, 'filtrarPorUsuario']);
        Route::get('logs/tabla/{tabla}', [LogController::class, 'filtrarPorTabla']);
        Route::get('logs/accion/{accion}', [LogController::class, 'filtrarPorAccion']);
        Route::get('logs/{log}', [LogController::class, 'show']);
    });
    Route::middleware('permiso:crear_logs')->group(function () {
        Route::post('logs', [LogController::class, 'store']);
    });
    
    // REPORTES
    Route::middleware('permiso:ver_reportes')->group(function () {
        Route::get('/reportes/ventas', [ReporteController::class, 'ventasPorFecha']);
        Route::get('/reportes/productos-mas-vendidos', [ReporteController::class, 'topProductosVendidos']);
        Route::get('/reportes/pagos/{estado}', [ReporteController::class, 'pagosPorEstado']);
        Route::get('/reportes/pdf/ventas', [ReportePdfController::class, 'ventasPorFecha']);
        Route::get('/reportes/pdf/productos-mas-vendidos', [ReportePdfController::class, 'productosMasVendidos']);
        Route::get('/reportes/pdf/stock-bajo', [ReportePdfController::class, 'stockBajo']);
        Route::get('/reportes/pdf/pagos/{estado}', [ReportePdfController::class, 'pdfPagosPorEstado']);
        Route::get('/reportes/pdf/facturas/{anio}/{mes}', [ReportePdfController::class, 'facturasPorMes']);
        Route::get('/reportes/pdf/boletas/{anioBoleta}/{mesBoleta}', [ReportePdfController::class, 'boletasPorMes']);
        Route::get('/reportes/facturas-count/{anio}/{mes}', [ReporteController::class, 'contarFacturas']);
        Route::get('/reportes/boletas-count/{anioBoleta}/{mesBoleta}', [ReporteController::class, 'contarBoletas']);
    });

    // ROLES
    Route::get('roles', [RolController::class, 'index'])->middleware('permiso:ver_roles');
    Route::post('roles', [RolController::class, 'store'])->middleware('permiso:crear_roles');
    Route::get('roles/{rol}', [RolController::class, 'show'])->middleware('permiso:ver_roles');
    Route::put('roles/{rol}', [RolController::class, 'update'])->middleware('permiso:editar_roles');
    Route::delete('roles/{rol}', [RolController::class, 'destroy'])->middleware('permiso:eliminar_roles');
    
    // PERMISOS
    Route::get('permisos', [PermisoController::class, 'index'])->middleware('permiso:ver_permisos');
    Route::post('permisos', [PermisoController::class, 'store'])->middleware('permiso:crear_permisos');
    Route::get('permisos/{permiso}', [PermisoController::class, 'show'])->middleware('permiso:ver_permisos');
    Route::put('permisos/{permiso}', [PermisoController::class, 'update'])->middleware('permiso:editar_permisos');
    Route::delete('permisos/{permiso}', [PermisoController::class, 'destroy'])->middleware('permiso:eliminar_permisos');

    // ASIGNACIÓN DE PERMISOS A ROLES
    Route::get('rol-permisos', [RolPermisoController::class, 'index'])->middleware('permiso:asignar_permisos');
    Route::post('rol-permisos', [RolPermisoController::class, 'store'])->middleware('permiso:asignar_permisos');
    Route::get('rol-permisos/{id}', [RolPermisoController::class, 'show'])->middleware('permiso:asignar_permisos');
    Route::put('rol-permisos/{id}', [RolPermisoController::class, 'update'])->middleware('permiso:asignar_permisos');
    Route::delete('rol-permisos/{id}', [RolPermisoController::class, 'destroy'])->middleware('permiso:asignar_permisos');
    Route::post('/rol-permisos/asignar/{rol}', [RolPermisoController::class, 'asignar'])->middleware('permiso:asignar_permisos');
    Route::get('/rol-permisos/{rol}/listar', [RolPermisoController::class, 'listar'])->middleware('permiso:asignar_permisos');

    // USUARIOS
    Route::get('usuarios', [UsuarioController::class, 'index'])->middleware('permiso:ver_usuarios');
    Route::post('usuarios', [UsuarioController::class, 'store'])->middleware('permiso:crear_usuarios');
    Route::get('usuarios/{usuario}', [UsuarioController::class, 'show'])->middleware('permiso:ver_usuarios');
    Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->middleware('permiso:editar_usuarios');
    Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->middleware('permiso:eliminar_usuarios');

    // CLIENTES
    Route::get('clientes', [ClienteController::class, 'index'])->middleware('permiso:ver_clientes');
    Route::post('clientes', [ClienteController::class, 'store'])->middleware('permiso:crear_clientes');
    Route::get('clientes/{cliente}', [ClienteController::class, 'show'])->middleware('permiso:ver_clientes');
    Route::put('clientes/{cliente}', [ClienteController::class, 'update'])->middleware('permiso:editar_clientes');
    Route::delete('clientes/{cliente}', [ClienteController::class, 'destroy'])->middleware('permiso:eliminar_clientes');
    
    // CATEGORÍAS
    Route::get('categorias', [CategoriaController::class, 'index'])->middleware('permiso:ver_categorias');
    Route::post('categorias', [CategoriaController::class, 'store'])->middleware('permiso:crear_categorias');
    Route::get('categorias/{categoria}', [CategoriaController::class, 'show'])->middleware('permiso:ver_categorias');
    Route::put('categorias/{categoria}', [CategoriaController::class, 'update'])->middleware('permiso:editar_categorias');
    Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])->middleware('permiso:eliminar_categorias');

    // PRODUCTOS
    Route::get('productos', [ProductoController::class, 'index'])->middleware('permiso:ver_productos');
    Route::post('productos', [ProductoController::class, 'store'])->middleware('permiso:crear_productos');
    Route::get('productos/{producto}', [ProductoController::class, 'show'])->middleware('permiso:ver_productos');
    Route::put('productos/{producto}', [ProductoController::class, 'update'])->middleware('permiso:editar_productos');
    Route::delete('productos/{producto}', [ProductoController::class, 'destroy'])->middleware('permiso:eliminar_productos');

    // STOCK
    Route::get('stock', [StockController::class, 'index'])->middleware('permiso:ver_stock');
    Route::post('stock', [StockController::class, 'store'])->middleware('permiso:crear_stock');
    Route::get('stock/{id}', [StockController::class, 'show'])->middleware('permiso:ver_stock');
    Route::put('stock/{stock}', [StockController::class, 'update'])->middleware('permiso:actualizar_stock');
    Route::delete('stock/{stock}', [StockController::class, 'destroy'])->middleware('permiso:eliminar_stock');

    // PEDIDOS
    Route::get('pedidos', [PedidoController::class, 'index'])->middleware('permiso:ver_pedidos');
    Route::post('pedidos', [PedidoController::class, 'store'])->middleware('permiso:crear_pedidos');
    Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->middleware('permiso:ver_pedidos');
    Route::put('pedidos/{pedido}', [PedidoController::class, 'update'])->middleware('permiso:editar_pedidos');
    Route::delete('pedidos/{pedido}', [PedidoController::class, 'destroy'])->middleware('permiso:eliminar_pedidos');

    // DETALLE PEDIDOS
    Route::get('detalle-pedidos', [DetallePedidoController::class, 'index'])->middleware('permiso:ver_pedidos');
    Route::post('detalle-pedidos', [DetallePedidoController::class, 'store'])->middleware('permiso:crear_pedidos');
    Route::get('detalle-pedidos/{id}', [DetallePedidoController::class, 'show'])->middleware('permiso:ver_pedidos');
    Route::put('detalle-pedidos/{id}', [DetallePedidoController::class, 'update'])->middleware('permiso:editar_pedidos');
    Route::delete('detalle-pedidos/{id}', [DetallePedidoController::class, 'destroy'])->middleware('permiso:eliminar_pedidos');


    // FACTURAS
    Route::get('facturas', [FacturaController::class, 'index'])->middleware('permiso:ver_facturas');
    Route::post('facturas', [FacturaController::class, 'store'])->middleware('permiso:crear_facturas');
    Route::get('facturas/{factura}', [FacturaController::class, 'show'])->middleware('permiso:ver_facturas');
    Route::put('facturas/{factura}', [FacturaController::class, 'update'])->middleware('permiso:editar_facturas');
    Route::delete('facturas/{factura}', [FacturaController::class, 'destroy'])->middleware('permiso:eliminar_facturas');

    // DETALLE FACTURA
    Route::get('detalle-facturas', [DetalleFacturaController::class, 'index'])->middleware('permiso:ver_facturas');
    Route::post('detalle-facturas', [DetalleFacturaController::class, 'store'])->middleware('permiso:crear_facturas');
    Route::get('detalle-facturas/{id}', [DetalleFacturaController::class, 'show'])->middleware('permiso:ver_facturas');
    Route::put('detalle-facturas/{id}', [DetalleFacturaController::class, 'update'])->middleware('permiso:editar_facturas');
    Route::delete('detalle-facturas/{id}', [DetalleFacturaController::class, 'destroy'])->middleware('permiso:eliminar_facturas');

    // BOLETAS
    Route::get('boletas', [BoletaController::class, 'index'])->middleware('permiso:ver_boletas');
    Route::post('boletas', [BoletaController::class, 'store'])->middleware('permiso:crear_boletas');
    Route::get('boletas/{boleta}', [BoletaController::class, 'show'])->middleware('permiso:ver_boletas');
    Route::put('boletas/{boleta}', [BoletaController::class, 'update'])->middleware('permiso:editar_boletas');
    Route::delete('boletas/{boleta}', [BoletaController::class, 'destroy'])->middleware('permiso:eliminar_boletas');

    // DETALLE BOLETA
    Route::get('detalle-boletas', [DetalleBoletaController::class, 'index'])->middleware('permiso:ver_boletas');
    Route::post('detalle-boletas', [DetalleBoletaController::class, 'store'])->middleware('permiso:crear_boletas');
    Route::get('detalle-boletas/{id}', [DetalleBoletaController::class, 'show'])->middleware('permiso:ver_boletas');
    Route::put('detalle-boletas/{id}', [DetalleBoletaController::class, 'update'])->middleware('permiso:editar_boletas');
    Route::delete('detalle-boletas/{id}', [DetalleBoletaController::class, 'destroy'])->middleware('permiso:eliminar_boletas');    

    // PAGOS
    Route::get('pagos', [PagoController::class, 'index'])->middleware('permiso:ver_pagos');
    Route::post('pagos', [PagoController::class, 'store'])->middleware('permiso:registrar_pagos');
    Route::put('pagos/{pago}', [PagoController::class, 'update'])->middleware('permiso:verificar_pagos');
    Route::get('pagos/{pago}', [PagoController::class, 'show'])->middleware('permiso:ver_pagos');
    Route::delete('pagos/{pago}', [PagoController::class, 'destroy'])->middleware('permiso:eliminar_pagos');

});

// Ruta libre de prueba
Route::get('/test', fn() => response()->json(['ok' => true]));
