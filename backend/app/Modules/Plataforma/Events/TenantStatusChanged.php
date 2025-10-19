<?php

namespace App\Modules\Plataforma\Events;

use App\Modules\Plataforma\Models\Tenant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tenant;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     *
     * @param Tenant $tenant
     * @param bool $oldStatus
     * @param bool $newStatus
     */
    public function __construct(Tenant $tenant, bool $oldStatus, bool $newStatus)
    {
        $this->tenant = $tenant;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
