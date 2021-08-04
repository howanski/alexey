<?php

namespace App\Service;

use Twig\TwigFunction;
use App\Class\SideMenuItem;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class AlexeySideMenuProvider extends AbstractExtension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Request
     */
    private $currentRequest;

    /**
     * @var string
     */
    private $currentRoute;

    /**
     * @var RouterInterface
     */
    private $router;

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

        $route = $this->router->generate('network_machine_index');
        $sideMenu[] = new SideMenuItem('Machines', $route, 'fa-server', $this->isActiveRoute($route));

        $route = $this->router->generate('weather');
        $sideMenu[] = new SideMenuItem('Weather', $route, 'fa-cloud-sun', $this->isActiveRoute($route));

        /**
         * Sample menu elements
         */
        // $sideMenu[] = $this->createDivider();
        // $sideMenu[] = $this->createHeading('One More Heading');

        // $withChildren = new SideMenuItem('OneWithChildren', '/', 'fa-wrench');
        // $childrenFirst = [];
        // $childrenFirst[] = $this->createHeading('PAparara 1:');
        // $childrenFirst[] = $this->createDivider();
        // $childrenFirst[] = new SideMenuItem('Charts', '/', 'fa-chart-area');
        // $childrenFirst[] = new SideMenuItem('Tables', '/', 'fa-table');
        // $withChildren->setChildren($childrenFirst);
        // $sideMenu[] = $withChildren;

        // $withChildrenActive = new SideMenuItem('WithChildren Active', '/', 'fa-wrench', true);
        // $childrenOfActive = [];
        // $childrenOfActive[] = $this->createHeading('PAparara 1:');
        // $childrenOfActive[] = new SideMenuItem('Charts', '/', 'fa-chart-area');
        // $childrenOfActive[] = $this->createDivider();
        // $childrenOfActive[] = new SideMenuItem('Tables', '/', 'fa-table');
        // $childrenOfActive[] = $this->createHeading('PAparara 2');

        // $withChildrenActive->setChildren($childrenOfActive);
        // $sideMenu[] = $withChildrenActive;


        // $sideMenu[] = new SideMenuItem('Charts', '/', 'fa-chart-area', true);
        // $sideMenu[] = new SideMenuItem('Tables', '/', 'fa-table');
        return $sideMenu;
    }


    /**
     * @param string $title
     * @return SideMenuItem
     */
    private function createHeading(string $title): SideMenuItem
    {
        $heading = new SideMenuItem();
        $heading->setIsHeading(true);
        $heading->setName($title);
        return $heading;
    }

    /**
     * @return SideMenuItem
     */
    private function createDivider(): SideMenuItem
    {
        $divider = new SideMenuItem();
        $divider->setIsDivider(true);
        return $divider;
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
