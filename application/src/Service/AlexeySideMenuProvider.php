<?php

declare(strict_types=1);

namespace App\Service;

use Twig\TwigFunction;
use App\Class\SideMenuItem;
use App\Service\AlexeyTranslator;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class AlexeySideMenuProvider extends AbstractExtension
{
    private ?Request $currentRequest;

    private ?string $currentRoute;

    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router,
        private AlexeyTranslator $translator,
    ) {
        $this->currentRequest = $this->requestStack->getCurrentRequest();
        if ($this->currentRequest instanceof Request) {
            $this->currentRoute = $this->currentRequest->getRequestUri();
        } else {
            $this->currentRoute = '/';
        }
        $this->router = $router;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('alexeySideMenu', [$this, 'exportMenuSchema']),
        ];
    }

    public function exportMenuSchema(): array
    {
        $sideMenu = [];

        $route = $this->router->generate('dashboard');
        $sideMenu[] = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'dashboard'
            ),
            destination: $route,
            icon: 'fas fa-yin-yang',
            isActive: $this->isActiveRoute($route),
            children: [],
        );

        $route = $this->router->generate('weather');
        $sideMenu[] = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'weather'
            ),
            destination: $route,
            icon: 'fas fa-cloud-sun',
            isActive: $this->isActiveRoute($route),
            children: [],
        );

        $sideMenu = $this->addNetworkMenuRecords($sideMenu);

        $sideMenu = $this->addMoneyMenuRecords($sideMenu);

        $route = $this->router->generate('crawler_index');
        $sideMenu[] = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'crawler'
            ),
            destination: $route,
            icon: 'fab fa-reddit-alien',
            isActive: $this->isActiveRoute($route),
            children: [],
        );

        return $sideMenu;
    }

    private function isActiveRoute(string $route): bool
    {
        if ($route === $this->currentRoute) {
            return true;
        }
        if ($this->currentRoute === '/' || $route === '/') {
            return false;
        }
        $strpos = strpos($this->currentRoute, $route);
        if ($strpos === false) {
            return false;
        }
        if ($strpos === 0) {
            return true;
        }
        return false;
    }

    private function addNetworkMenuRecords(array $sideMenu): array
    {
        $route = $this->router->generate('network_machine_index');
        $networkMachines = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'network_machines'
            ),
            destination: $route,
            icon: 'fas fa-robot',
            isActive: $this->isActiveRoute($route),
            children: [],
        );

        $route = $this->router->generate('network_usage');
        $networkUsage = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'network_usage'
            ),
            destination: $route,
            icon: 'fas fa-tachometer-alt',
            isActive: $this->isActiveRoute($route),
            children: [],
        );

        $route = $this->router->generate('network_transmission');
        $networkTransmission = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record_transmission',
                module: 'network_usage'
            ),
            destination: $route,
            icon: 'fas fa-cloud-download-alt',
            isActive: $this->isActiveRoute($route),
            children: [],
        );


        $route = '/network';
        $menuItem = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'network'
            ),
            destination: $route,
            icon: 'fas fa-wifi',
            isActive: $this->isActiveRoute($route),
            children: [
                $networkMachines,
                $networkUsage,
                $networkTransmission,
            ],
        );

        $sideMenu[] = $menuItem;

        return $sideMenu;
    }

    private function addMoneyMenuRecords(array $sideMenu): array
    {


        $route = $this->router->generate('money_node_index');
        $moneyNodes = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record_nodes',
                module: 'money'
            ),
            destination: $route,
            icon: 'fas fa-hand-holding-usd',
            isActive: $this->isActiveRoute($route),
            children: [],
        );

        $route = $this->router->generate('money_transfer_index');
        $moneyTransfers = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record_transfers',
                module: 'money'
            ),
            icon: 'fas fa-exchange-alt',
            destination: $route,
            isActive: $this->isActiveRoute($route),
            children: [],
        );

        $route = $this->router->generate('money_graph_nodes');
        $moneyGraphs = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record_graphs',
                module: 'money'
            ),
            icon: 'fas fa-chart-line',
            destination: $route,
            isActive: $this->isActiveRoute($route),
            children: [],
        );


        $route = '/money';
        $menuItem = new SideMenuItem(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'money'
            ),
            destination: $route,
            icon: 'fas fa-search-dollar',
            isActive: $this->isActiveRoute($route),
            children: [
                $moneyNodes,
                $moneyTransfers,
                $moneyGraphs,
            ],
        );


        $sideMenu[] = $menuItem;

        return $sideMenu;
    }
}
