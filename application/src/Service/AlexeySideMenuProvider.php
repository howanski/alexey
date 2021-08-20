<?php

declare(strict_types=1);

namespace App\Service;

use Twig\TwigFunction;
use App\Class\SideMenuItem;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AlexeySideMenuProvider extends AbstractExtension
{
    private RequestStack $requestStack;

    private Request $currentRequest;

    private string $currentRoute;

    private RouterInterface $router;

    public function __construct(RequestStack $requestStack, RouterInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->currentRequest = $this->requestStack->getCurrentRequest();
        /**
         * currentRequest will be empty on CLI mode
         * Sometimes we'd like to run app via CLI with Twig enabled, which would case app crash in following lines
         */
        if ($this->currentRequest) {
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

    /**
     * @return []SideMenuItem
     */
    public function exportMenuSchema(): array
    {
        $sideMenu = [];

        $route = $this->router->generate('dashboard');
        $sideMenu[] = new SideMenuItem('Dashboard', $route, 'fa-tachometer-alt', $this->isActiveRoute($route));


        $route = $this->router->generate('weather');
        $sideMenu[] = new SideMenuItem('Weather', $route, 'fa-cloud-sun', $this->isActiveRoute($route));


        $menuItem = new SideMenuItem('Network', '/network', 'fa-wifi', $this->isActiveRoute('/network'));

        $route = $this->router->generate('network_machine_index');
        $networkMachines = new SideMenuItem('Machines', $route, 'fa-server', $this->isActiveRoute($route));

        $route = $this->router->generate('network_usage');
        $networkUsage = new SideMenuItem('Usage', $route, 'fa-network-wired', $this->isActiveRoute($route));

        $route = $this->router->generate('network_transmission');
        $networkTransmission = new SideMenuItem('Transmission', $route, 'fa-network-wired', $this->isActiveRoute($route));

        $menuItem->setChildren([$networkMachines, $networkUsage, $networkTransmission]);
        $sideMenu[] = $menuItem;
        return $sideMenu;
    }

    private function isActiveRoute(string $route): bool
    {
        if ($route == $this->currentRoute) {
            return true;
        }
        if ($this->currentRoute == '/' || $route == '/') {
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
