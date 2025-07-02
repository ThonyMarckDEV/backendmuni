<?php

use App\Http\Controllers\ActivoAreaController;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidenteController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rutas públicas (no requieren autenticación)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/validate-refresh-token', [AuthController::class, 'validateRefreshToken']);
Route::post('/usuarios', [UserController::class, 'store']);

// RUTAS PARA ADMIN VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:admin'])->group(function () { 
    // User routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{id}/estado', [UserController::class, 'cambiarEstado'])->name('users.cambiarEstado');

    // Role routes
    Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RolController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}', [RolController::class, 'show'])->name('roles.show');
    Route::put('/roles/{id}', [RolController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RolController::class, 'destroy'])->name('roles.destroy');

    // Activo routes
    Route::get('/activos', [ActivoController::class, 'index']);
    Route::get('/activoslistar', [ActivoController::class, 'index2']);
    Route::post('/activos', [ActivoController::class, 'store']);
    Route::put('/activos/{id}', [ActivoController::class, 'update']);
    Route::get('/activos/{id}', [ActivoController::class, 'show']);

    //Areas routes
    Route::get('/areas', [AreaController::class, 'index']);
    Route::get('/areaslistar', [AreaController::class, 'index2']);
    Route::get('/areas/{id}', [AreaController::class, 'show']);
    Route::post('/areas', [AreaController::class, 'store']);
    Route::put('/areas/{id}', [AreaController::class, 'update']);
    Route::delete('/areas/{id}', [AreaController::class, 'destroy']);

    // Activos-Areas routes
    Route::get('/areas/{idArea}/activos', [ActivoAreaController::class, 'index']);
    Route::post('/activos-areas', [ActivoAreaController::class, 'store']);
    Route::put('/activos-areas/{id}', [ActivoAreaController::class, 'update']);
    Route::delete('/activos-areas/{id}', [ActivoAreaController::class, 'destroy']);
    Route::get('/getactivos', [ActivoAreaController::class, 'indexActivos']);

    // Rutas Incidentes
    Route::get('/incidentes/tecnicos', [IncidenteController::class, 'getTechnicians'])->name('incidentes.tecnicos');

    //Rutas Dashboard
    Route::get('/users-by-area', [DashboardController::class, 'getUsersByArea']);
    Route::get('/areas-data', [DashboardController::class, 'getAreasData']);
});


// RUTAS PARA Todos los Roles
Route::middleware(['auth.jwt', 'checkRolesMW'])->group(function () { 
    Route::post('/logout', [AuthController::class, 'logout']);

    // Incidente routes
    Route::get('/incidentes', [IncidenteController::class, 'index']);
    Route::post('/incidentes', [IncidenteController::class, 'store']);
    Route::get('/incidentes/getactivos', [IncidenteController::class, 'getActivos']);
    Route::get('/incidentes/{id}', [IncidenteController::class, 'show']);
    Route::put('/incidentes/{id}', [IncidenteController::class, 'update']);
    Route::get('/incidentes/{id}/pdf', [IncidenteController::class, 'generatePdf']);
    Route::get('/userArea', [IncidenteController::class, 'getUserData']);

    //Dashaoard routes
    Route::get('/incidents-data', [DashboardController::class, 'getIncidentsData']);

});

// RUTAS PARA Rol Admin y Usuario
Route::middleware(['auth.jwt', 'CheckRolesAdmin_Usuario'])->group(function () { 

    Route::get('/assets-by-area', [DashboardController::class, 'getActivosporArea']);

});
