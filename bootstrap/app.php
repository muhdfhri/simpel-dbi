<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Inertia: handle Inertia requests di setiap web request
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Spatie RBAC: alias role & permission untuk route group (ARCHITECTURE.md Section 5)
        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, \Illuminate\Http\Request $request) {
            $roleVal = $request->user()?->role;
            $roleStr = $roleVal instanceof \App\Enums\UserRole ? $roleVal->value : ($roleVal ?? 'desa');
            $dashboardRoute = "/{$roleStr}/dashboard";

            return redirect()->to($dashboardRoute)->with('warning', 'Hak akses untuk fitur ini sedang dinonaktifkan sementara oleh Administrator Kanwil Ditjen Imigrasi Sumut.');
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 404) {
                return \Inertia\Inertia::render('Errors/404')->toResponse($request)->setStatusCode(404);
            }

            if ($e->getStatusCode() === 403) {
                $roleVal = $request->user()?->role;
                $roleStr = $roleVal instanceof \App\Enums\UserRole ? $roleVal->value : ($roleVal ?? 'desa');
                $dashboardRoute = "/{$roleStr}/dashboard";

                return redirect()->to($dashboardRoute)->with('warning', 'Hak akses untuk fitur ini sedang dinonaktifkan sementara oleh Administrator Kanwil Ditjen Imigrasi Sumut.');
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            return \Inertia\Inertia::render('Errors/404')->toResponse($request)->setStatusCode(404);
        });
    })->create();
