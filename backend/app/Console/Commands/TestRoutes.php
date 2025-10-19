<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class TestRoutes extends Command
{
    protected $signature = 'test:routes';
    protected $description = 'Test if routes are registered';

    public function handle()
    {
        $this->info('Testing routes...');
        
        $routes = Route::getRoutes();
        $contatoRoutes = [];
        
        foreach ($routes as $route) {
            if (str_contains($route->uri(), 'contatos')) {
                $contatoRoutes[] = [
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName()
                ];
            }
        }
        
        if (empty($contatoRoutes)) {
            $this->error('No contato routes found!');
        } else {
            $this->info('Found contato routes:');
            foreach ($contatoRoutes as $route) {
                $this->line("  {$route['method']} {$route['uri']} -> {$route['action']}");
            }
        }
        
        return 0;
    }
}