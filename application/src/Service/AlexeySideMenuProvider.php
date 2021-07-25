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

    /**
     * @return []SideMenuItem
     */
    public function exportMenuSchema(): array
    {
        $sideMenu = [];

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
}
