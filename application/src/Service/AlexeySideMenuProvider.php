<?php

namespace App\Service;

use App\Class\SideMenuItem;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class AlexeySideMenuProvider extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('alexeySideMenu', [$this, 'exportMenuSchema']),
        ];
    }

    public function exportMenuSchema()
    {
        $sideMenu = [];
        $sideMenu[] = new SideMenuItem('Charts', '/', 'fa-chart-area', false);
        $sideMenu[] = new SideMenuItem('Tables', '/', 'fa-table', false);
        return $sideMenu;
    }
}
