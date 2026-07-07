<?php

namespace Plugins\Maintenance;

use Illuminate\Support\ServiceProvider;
use App\Support\Settings;

class MaintenanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Registra a pasta de views do plugin com namespace "maintenance"
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'maintenance');

        // 2. Injeta um novo grupo de configurações (aba de admin)
        Settings::addGroup('maintenance', [
            'title'       => 'Manutenção',
            'description' => 'Configurações de bloqueio do site público.',
            'icon'        => 'construction',
        ]);

        // 3. Injeta as chaves funcionais sob o novo grupo
        Settings::add([
            'key' => 'maintenance_enabled',
            'type' => 'switch',
            'active' => 'Ativado',
            'inactive' => 'Desativado',
            'label' => 'Ativar modo de manutenção',
            'description' => 'Quando ativado, bloqueia o acesso dos visitantes ao site público.',
            'default' => false,
        ], 'maintenance');

        Settings::add([
            'key' => 'maintenance_icon',
            'type' => 'icon',
            'label' => 'Ícone no topo',
            'description' => 'Ícone exibido em destaque no topo página de bloqueio.',
            'default' => 'wrench',
            'can_clear' => false,
            'depends_on' => [
                'field' => 'maintenance_enabled',
                'operator' => '===',
                'value' => true,
            ],
        ], 'maintenance');

        Settings::add([
            'key' => 'maintenance_title',
            'type' => 'text',
            'label' => 'Título da página',
            'description' => 'Título exibido em destaque na página de bloqueio.',
            'default' => 'Site em Manutenção',
            'depends_on' => [
                'field' => 'maintenance_enabled',
                'operator' => '===',
                'value' => true,
            ],
        ], 'maintenance');

        Settings::add([
            'key' => 'maintenance_text',
            'type' => 'textarea',
            'label' => 'Mensagem de bloqueio',
            'description' => 'Aviso ou explicação exibida aos visitantes do site.',
            'default' => 'Estamos trabalhando em novidades e melhorias no nosso site. Voltamos em instantes!',
            'depends_on' => [
                'field' => 'maintenance_enabled',
                'operator' => '===',
                'value' => true,
            ],
        ], 'maintenance');

        Settings::add([
            'key' => 'maintenance_footer_icon',
            'type' => 'icon',
            'label' => 'Ícone no footer',
            'description' => 'Ícone exibido no footer.',
            'default' => '',
            'depends_on' => [
                'field' => 'maintenance_enabled',
                'operator' => '===',
                'value' => true,
            ],
        ], 'maintenance');

        Settings::add([
            'key' => 'maintenance_footer_text',
            'type' => 'text',
            'label' => 'Texto no footer',
            'description' => 'Texto que aparece no footer. Padrão: Equipe [site_name].',
            'default' => '',
            'depends_on' => [
                'field' => 'maintenance_enabled',
                'operator' => '===',
                'value' => true,
            ],
        ], 'maintenance');

        // 4. Acopla o middleware de bloqueio no final do grupo de rotas 'web' do Laravel
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', \Plugins\Maintenance\Http\Middleware\CheckMaintenanceMode::class);
    }
}
