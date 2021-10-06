<?php

declare(strict_types=1);

namespace App\Service;

use Twig\TwigFunction;
use App\Class\SideMenuItem;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class AlexeySideMenuProvider extends AbstractExtension
{

    private ?Request $currentRequest;

    private ?string $currentRoute;

    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router,
        private TranslatorInterface $translator,
    ) {
        $this->currentRequest = $this->requestStack->getCurrentRequest();
        $this->currentRoute = $this?->currentRequest?->getRequestUri();
        if (is_null($this->currentRoute)) {
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

    /**
     * @return []SideMenuItem
     */
    public function exportMenuSchema(): array
    {
        $sideMenu = [];

        $route = $this->router->generate('dashboard');
        $sideMenu[] = new SideMenuItem(
            name: $this->translator->trans('app.dashboard'),
            destination: $route,
            icon: 'fa-tachometer-alt',
            isActive: $this->isActiveRoute($route),
        );


        $route = $this->router->generate('weather');
        $sideMenu[] = new SideMenuItem(
            name: $this->translator->trans('app.weather'),
            destination: $route,
            icon: 'fa-cloud-sun',
            isActive: $this->isActiveRoute($route),
        );


        $route = '/network';
        $menuItem = new SideMenuItem(
            name: $this->translator->trans('app.network'),
            destination: $route,
            icon: 'fa-wifi',
            isActive: $this->isActiveRoute($route),
        );

        $route = $this->router->generate('network_machine_index');
        $networkMachines = new SideMenuItem(
            name: $this->translator->trans('app.network_machines'),
            destination: $route,
            isActive: $this->isActiveRoute($route),
        );

        $route = $this->router->generate('network_usage');
        $networkUsage = new SideMenuItem(
            name: $this->translator->trans('app.network_usage'),
            destination: $route,
            isActive: $this->isActiveRoute($route),
        );

        $route = $this->router->generate('network_transmission');
        $networkTransmission = new SideMenuItem(
            name: $this->translator->trans('app.network_transmission'),
            destination: $route,
            isActive: $this->isActiveRoute($route),
        );

        $menuItem->setChildren([$networkMachines, $networkUsage, $networkTransmission]);
        $sideMenu[] = $menuItem;
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
}
