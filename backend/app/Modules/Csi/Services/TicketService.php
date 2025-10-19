<?php

namespace App\Modules\Csi\Services;

use App\Modules\Csi\Interfaces\TicketRepositoryInterface;
use App\Modules\Csi\Interfaces\InteracaoRepositoryInterface;
use Carbon\Carbon;

class TicketService
{
    protected $ticketRepository;
    protected $interacaoRepository;

    public function __construct(
        TicketRepositoryInterface $ticketRepository,
        InteracaoRepositoryInterface $interacaoRepository
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->interacaoRepository = $interacaoRepository;
    }

    /**
     * Criar um novo ticket
     */
    public function createTicket(array $data)
    {
        // Definir data de abertura se não fornecida
        if (!isset($data['data_abertura'])) {
            $data['data_abertura'] = Carbon::now();
        }

        // Status padrão
        if (!isset($data['status'])) {
            $data['status'] = 'aberto';
        }

        $ticket = $this->ticketRepository->create($data);

        // Criar interação inicial
        $this->interacaoRepository->create([
            'tenant_id' => $ticket->tenant_id,
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'tipo' => 'comentario',
            'conteudo' => 'Ticket criado: ' . $ticket->titulo,
            'visivel_cliente' => true,
            'data_interacao' => $ticket->data_abertura
        ]);

        return $ticket;
    }

    /**
     * Atualizar status do ticket
     */
    public function updateStatus($ticketId, $newStatus, $userId)
    {
        $ticket = $this->ticketRepository->findById($ticketId);
        $oldStatus = $ticket->status;

        // Atualizar ticket
        $updateData = ['status' => $newStatus];
        
        // Se fechando o ticket, definir data de fechamento
        if ($newStatus === 'fechado' && !$ticket->data_fechamento) {
            $updateData['data_fechamento'] = Carbon::now();
            
            // Calcular tempo de resolução em horas
            if ($ticket->data_abertura) {
                $updateData['tempo_resolucao'] = Carbon::parse($ticket->data_abertura)
                    ->diffInHours(Carbon::now());
            }
        }

        $ticket = $this->ticketRepository->update($ticketId, $updateData);

        // Criar interação de mudança de status
        $this->interacaoRepository->create([
            'tenant_id' => $ticket->tenant_id,
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'tipo' => 'mudanca_status',
            'conteudo' => "Status alterado de '{$oldStatus}' para '{$newStatus}'",
            'visivel_cliente' => true,
            'data_interacao' => Carbon::now()
        ]);

        return $ticket;
    }

    /**
     * Atribuir ticket a um usuário
     */
    public function assignTicket($ticketId, $assignedTo, $userId)
    {
        $ticket = $this->ticketRepository->findById($ticketId);
        $oldAssigned = $ticket->assigned_to;

        $ticket = $this->ticketRepository->update($ticketId, [
            'assigned_to' => $assignedTo,
            'status' => 'em_andamento'
        ]);

        // Criar interação de atribuição
        $message = $oldAssigned 
            ? "Ticket reatribuído para usuário ID: {$assignedTo}"
            : "Ticket atribuído para usuário ID: {$assignedTo}";

        $this->interacaoRepository->create([
            'tenant_id' => $ticket->tenant_id,
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'tipo' => 'mudanca_status',
            'conteudo' => $message,
            'visivel_cliente' => false,
            'data_interacao' => Carbon::now()
        ]);

        return $ticket;
    }

    /**
     * Adicionar comentário ao ticket
     */
    public function addComment($ticketId, $userId, $conteudo, $visivelCliente = true, $anexos = null)
    {
        $ticket = $this->ticketRepository->findById($ticketId);

        $interacao = $this->interacaoRepository->create([
            'tenant_id' => $ticket->tenant_id,
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'tipo' => 'comentario',
            'conteudo' => $conteudo,
            'anexos' => $anexos,
            'visivel_cliente' => $visivelCliente,
            'data_interacao' => Carbon::now()
        ]);

        return $interacao;
    }

    /**
     * Obter estatísticas de tickets por status
     */
    public function getTicketStats($tenantId = null)
    {
        $filters = [];
        if ($tenantId) {
            $filters['tenant_id'] = $tenantId;
        }

        $stats = [
            'aberto' => 0,
            'em_andamento' => 0,
            'aguardando' => 0,
            'resolvido' => 0,
            'fechado' => 0,
            'total' => 0
        ];

        $statuses = ['aberto', 'em_andamento', 'aguardando', 'resolvido', 'fechado'];
        
        foreach ($statuses as $status) {
            $count = $this->ticketRepository->search(
                array_merge($filters, ['status' => $status]), 
                false
            )->count();
            
            $stats[$status] = $count;
            $stats['total'] += $count;
        }

        return $stats;
    }
}
