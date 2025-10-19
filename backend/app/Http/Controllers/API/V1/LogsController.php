<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Interfaces\LogRepositoryInterface;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LogsController extends Controller
{
    protected $logRepository;
    protected $logService;

    public function __construct(
        LogRepositoryInterface $logRepository,
        LogService $logService
    )
    {
        $this->logRepository = $logRepository;
        $this->logService = $logService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $tenantId = Auth::user()->tenant_id;
        
        // Se usuário tem permissão global (HUB), pode ver todos os tenants
        if (Auth::user()->can('logs.view.all')) {
            $tenantId = $request->input('tenant_id', $tenantId);
        }

        $logs = $this->logRepository->getAll($perPage, $tenantId);
        return response()->json($logs);
    }

    public function show($id)
    {
        $tenantId = Auth::user()->tenant_id;
        
        // Se usuário tem permissão global (HUB), pode ver todos os tenants
        if (Auth::user()->can('logs.view.all')) {
            $tenantId = request()->input('tenant_id', $tenantId);
        }
        
        $log = $this->logRepository->findById($id, $tenantId);
        
        if(!$log){
            return response()->json(['message' => 'Log não encontrado'], Response::HTTP_NOT_FOUND);
        }
        
        return response()->json($log);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|max:255',
            'description' => 'nullable|string',
            'log_type' => 'nullable|in:info,error,warning,debug',
            'status' => 'nullable|in:active,inactive,processed',
            'content' => 'nullable|string',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['user_id'] = Auth::id();
        $validated['ip_address'] = $request->ip();

        $log = $this->logRepository->create($validated);
        return response()->json($log, Response::HTTP_CREATED);
    }

    public function destroy(Request $request, $id){
        $tenantId = Auth::user()->tenant_id;
        
        // Se usuário tem permissão global (HUB), pode especificar tenant
        if (Auth::user()->can('logs.view.all')) {
            $tenantId = $request->input('tenant_id', $tenantId);
        }
        
        $log = $this->logRepository->findById($id, $tenantId);
        
        if(!$log){
            return response()->json(['message' => 'Log não encontrado'], Response::HTTP_NOT_FOUND);
        }

        $this->logRepository->delete($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function search(Request $request)
    {
        $filters = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'action' => 'nullable|string',
            'log_type' => 'nullable|in:info,error,warning,debug',
            'status' => 'nullable|in:active,inactive,processed',
            'created_at_from' => 'nullable|date',
            'created_at_to' => 'nullable|date|after:created_at_from',
            'content' => 'nullable|string',
        ]);

        $filters['tenant_id'] = Auth::user()->tenant_id;
        $perPage = $request->input('per_page', 15);
        
        $logs = $this->logRepository->search($filters, $perPage);
        return response()->json($logs);
    }

    public function dashboard(Request $request){
        $tenantId = Auth::user()->tenant_id;
        
        // Se usuário tem permissão global (HUB), pode ver todos os tenants
        if (Auth::user()->can('logs.view.all')) {
            $tenantId = $request->input('tenant_id', $tenantId);
        }
        
        $stats = $this->logService->getDashboardStats($tenantId);

        return response()->json($stats);
    }

    public function hourlyTrend(Request $request){
        $tenantId = Auth::user()->tenant_id;
        
        // Se usuário tem permissão global (HUB), pode ver todos os tenants
        if (Auth::user()->can('logs.view.all')) {
            $tenantId = $request->input('tenant_id', $tenantId);
        }
        
        $trend = $this->logService->getHourlyTrend($tenantId);
        
        return response()->json($trend);
    }

    public function logsByPeriod(Request $request){
        $tenantId = Auth::user()->tenant_id;
        
        // Se usuário tem permissão global (HUB), pode ver todos os tenants
        if (Auth::user()->can('logs.view.all')) {
            $tenantId = $request->input('tenant_id', $tenantId);
        }
        
        $days = $request->input('days', 7);
        $logs = $this->logService->getLogsByPeriod($tenantId, $days);
        return response()->json($logs);
    }
    

    public function errors(Request $request){
        $tenantId = $request->input('tenants_id', Auth::user()->tenant_id);
        $perPage = $request->input('per_page', 15);

        $logs = $this->LogRepository->getErrors($perPage, $tenantId);

        return response()->json($logs);
    }

    public function byType(Request $request, $type){
        $validateType = $request->validate([
            'type' => 'required|in:info,error,warning,debug',
        ]);

        $tenantId = $request->input('tenants_id', Auth::user()->tenant_id);
        $perPage = $request->input('per_page', 15);

        $logs = $this->LogRepository->getByType($validateType['type'], $perPage, $tenantId);

        return response()->json($logs);
    }

    /**
     * Retorna lista de operações únicas disponíveis nos logs (CREATE, UPDATE, DELETE, etc)
     */
    public function getActions(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        // Se usuário tem permissão global (HUB), pode ver todos os tenants
        if (Auth::user()->can('logs.view.all')) {
            $tenantId = $request->input('tenant_id', $tenantId);
        }
        
        $actions = $this->logRepository->getUniqueActions($tenantId);
        
        return response()->json($actions);
    }
} 