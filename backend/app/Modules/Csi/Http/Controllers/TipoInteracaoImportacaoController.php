<?php

namespace App\Modules\Csi\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Csi\Models\TipoInteracao;
use App\Modules\Csi\Services\Imports\ERP\ErpIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TipoInteracaoImportacaoController extends Controller
{
    // Novo endpoint para importar um único registro manualmente
    public function importarRegistro(Request $request)
    {
        $request->validate([
            'tipo_interacao_id' => 'required|integer|exists:tipos_interacao,id',
            'registro' => 'required|array',
            'registro.saiIntCodCli' => 'required',
            'registro.saiDatDatItr' => 'required',
            'registro.saiStrTitItr' => 'required',
        ]);

        $tipo = \App\Modules\Csi\Models\TipoInteracao::with('conector')->findOrFail($request->input('tipo_interacao_id'));
        $service = app(\App\Modules\Csi\Services\Imports\ERP\ErpIntegrationService::class);

        try {
            $service->criarInteracao($tipo, $request->input('registro'));
            return response()->json(['success' => true, 'message' => 'Registro importado/atualizado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    public function importarErp($id, Request $request)
    {
        $tipo = TipoInteracao::with('conector')->findOrFail($id);
        $service = app(ErpIntegrationService::class);
        $resultado = $service->processarTipoInteracao($tipo);

        // Exemplo de resposta: ['sucesso' => 10, 'erro' => 2, 'mensagens' => [...]]
        return response()->json([
            'total' => ($resultado['sucesso'] ?? 0) + ($resultado['erro'] ?? 0),
            'sucesso' => $resultado['sucesso'] ?? 0,
            'erro' => $resultado['erro'] ?? 0,
            'mensagens' => $resultado['mensagens'] ?? [],
        ]);
    }
}
