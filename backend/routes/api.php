<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
| As rotas API estão organizadas por módulos e são registradas
| diretamente no bootstrap/app.php:
| - routes/plataforma.php (Auth, Users, Perfis, Tenants, Permissions)
| - routes/clientes.php (Clientes)
| - routes/interacoes.php (Interações, Logs)
|
*/

// As rotas modulares são registradas diretamente no bootstrap/app.php
// Este arquivo pode ser usado para rotas API adicionais se necessário