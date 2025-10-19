<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Plataforma\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'nome' => 'Sancon',
                'status' => true,
                'dominio' => 'sancon'
            ],
            [
                'nome' => 'Bragagnolo',
                'status' => true,
                'dominio' => 'bragagnolo'
            ],
            [
                'nome' => 'Cotramol',
                'status' => true,
                'dominio' => 'cotramol'
            ],
            [
                'nome' => 'Berlanda',
                'status' => true,
                'dominio' => 'berlanda'
            ],
            [
                'nome' => 'Agrodanieli',
                'status' => false,
                'dominio' => 'agrodanieli'
            ]
        ];

        foreach ($tenants as $tenantData) {
            Tenant::create($tenantData);
        }
    }
}
