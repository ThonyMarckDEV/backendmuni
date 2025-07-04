<?php

use App\Http\Middleware\CheckRoleMW;
use App\Http\Middleware\CheckRolesAdmin_Usuario;
use App\Http\Middleware\CheckRolesMW;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\JWTAuthMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    //ACA REGISTRAMOS MIDDLEWARES
    ->withMiddleware(function (Middleware $middleware) {
        $middleware ->alias([
            //MIDDLEWARE PARA VERIFICAR ROL INDIVIDUAL
            'checkRoleMW' =>CheckRoleMW::class,
            //MIDDLEWARE PARA VERIFICAR ROL MULTIPLE
            'checkRolesMW' =>CheckRolesMW::class,
            //MIDDLEWARE PARA VERIFICAR ROL ADMIN Y USUARIO
            'CheckRolesAdmin_Usuario'=>CheckRolesAdmin_Usuario::class,
            //MIDDLEWARE PARA AUTH DEL TOKEN
            'auth.jwt' =>JWTAuthMiddleware::class,
            //MIDDLEWARE CORS
            'cors' =>CorsMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
