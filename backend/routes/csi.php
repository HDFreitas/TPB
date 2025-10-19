<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Modules\Csi\Controllers\ClientesController;
use App\Modules\Csi\Controllers\InteracoesController;
use App\Modules\Csi\Controllers\ContatosController;
use App\Modules\Csi\Controllers\ConectoresController;
use App\Modules\Csi\Controllers\TiposInteracaoController;

/*
|--------------------------------------------------------------------------
| Rotas do Módulo CSI (Centro de Serviços de TI)
|--------------------------------------------------------------------------
|
| Aqui estão as rotas relacionadas ao sistema de clientes
| e interações do Centro de Serviços de TI.
|
*/

// Rota de teste sem middleware
Route::get('/test-contatos', function () {
    return response()->json(['message' => 'Contatos route working!']);
});

// Rota de teste para inserir contato sem middleware
Route::post('/test-contatos', function (Request $request) {
    $data = $request->all();
    $data['created_by'] = 1; // ID do usuário admin
    $data['updated_by'] = 1;
    
    $contato = \App\Modules\Csi\Models\Contato::create($data);
    
    return response()->json([
        'message' => 'Contato criado com sucesso!',
        'data' => $contato
    ]);
});

Route::prefix('v1')->middleware('jwt')->group(function () {
    
    // ==================== CLIENTES ====================
    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClientesController::class, 'index']);
        Route::post('/', [ClientesController::class, 'store']);
        Route::get('/search', [ClientesController::class, 'search']);
        Route::post('/search', [ClientesController::class, 'search']);
        Route::get('/tenant/{tenantId}', [ClientesController::class, 'byTenant']);
        Route::get('/cnpj-cpf/{cnpjCpf}', [ClientesController::class, 'byCnpjCpf']);
        Route::get('/{id}', [ClientesController::class, 'show']);
        Route::put('/{id}', [ClientesController::class, 'update']);
        Route::delete('/{id}', [ClientesController::class, 'destroy']);
    });

    // ==================== INTERAÇÕES ====================
    Route::prefix('interacoes')->group(function () {
        Route::get('/', [InteracoesController::class, 'index']);
        Route::post('/', [InteracoesController::class, 'store']);
        Route::get('/search', [InteracoesController::class, 'search']);
        Route::post('/search', [InteracoesController::class, 'search']);
        Route::get('/cliente/{clienteId}', [InteracoesController::class, 'byCliente']);
        Route::get('/tenant/{tenantId}', [InteracoesController::class, 'byTenant']);
        Route::get('/{id}', [InteracoesController::class, 'show']);
        Route::put('/{id}', [InteracoesController::class, 'update']);
        Route::delete('/{id}', [InteracoesController::class, 'destroy']);
    });

    // ==================== CONECTORES ====================
    Route::prefix('conectores')->group(function () {
        Route::get('/', [ConectoresController::class, 'index']);
        Route::get('/search', [ConectoresController::class, 'search']);
        Route::post('/search', [ConectoresController::class, 'search']);
        Route::get('/ativos', [ConectoresController::class, 'ativos']);
        Route::get('/tenant/{tenantId}', [ConectoresController::class, 'byTenant']);
        Route::get('/codigo/{codigo}', [ConectoresController::class, 'byCodigo']);
        Route::get('/codigo/{codigo}/tenant/{tenantId}', [ConectoresController::class, 'byCodigoAndTenant']);
        Route::get('/{id}', [ConectoresController::class, 'show']);
        Route::put('/{id}', [ConectoresController::class, 'update']);
        Route::post('/{id}/test-connection', [ConectoresController::class, 'testConnection']);
    });

    // ==================== CONTATOS ====================
    Route::prefix('contatos')->group(function () {
        Route::get('/', [ContatosController::class, 'index']);
        Route::post('/', [ContatosController::class, 'store']);
        Route::get('/search', [ContatosController::class, 'search']);
        Route::post('/search', [ContatosController::class, 'search']);
        Route::get('/cliente/{clienteId}', [ContatosController::class, 'byCliente']);
        Route::get('/tenant/{tenantId}', [ContatosController::class, 'byTenant']);
        Route::get('/{id}', [ContatosController::class, 'show']);
        Route::put('/{id}', [ContatosController::class, 'update']);
        Route::delete('/{id}', [ContatosController::class, 'destroy']);
    });

    // ==================== TIPOS DE INTERAÇÃO ====================
    Route::prefix('tipos-interacao')->group(function () {
    Route::get('/', [TiposInteracaoController::class, 'index']);
    Route::post('/', [TiposInteracaoController::class, 'store']);
    Route::get('/search', [TiposInteracaoController::class, 'search']);
    Route::post('/search', [TiposInteracaoController::class, 'search']);
    // Importação ERP para tipo de interação
    Route::post('/{id}/importar-erp', [\App\Modules\Csi\Http\Controllers\TipoInteracaoImportacaoController::class, 'importarErp']);
        Route::get('/ativos', [TiposInteracaoController::class, 'ativos']);
        Route::get('/tenant/{tenantId}', [TiposInteracaoController::class, 'byTenant']);
        Route::get('/conector/{conectorId}', [TiposInteracaoController::class, 'byConector']);
        Route::get('/{id}', [TiposInteracaoController::class, 'show']);
        Route::put('/{id}', [TiposInteracaoController::class, 'update']);
        Route::delete('/{id}', [TiposInteracaoController::class, 'destroy']);
        Route::post('/{id}/import', [TiposInteracaoController::class, 'importFromConector']);
    });

});
