<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Class\SideMenuItem;
use PHPUnit\Framework\TestCase;
use App\Service\AlexeySideMenuProvider;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @covers App\Service\AlexeySideMenuProvider
 */
final class AlexeySideMenuProviderTest extends TestCase
{
    public function testExportMenuSchema(): void
    {
        $requestStack = $this->createMock(originalClassName: RequestStack::class);
        $router = $this->createMock(originalClassName: RouterInterface::class);

        $routerCallback = function (string $path) {
            return '/' . $path;
        };

        $router->method('generate')->willReturnCallback($routerCallback);

        $service = new AlexeySideMenuProvider(
            requestStack: $requestStack,
            router: $router
        );
        $menu = $service->exportMenuSchema();

        $this->assertIsArray(
            actual: $menu,
            message: '---!---> Wrong menu construction',
        );

        $this->assertEquals(
            expected: 3,
            actual: sizeof($menu),
            message: '---!---> Not enough main menu items',
        );

        $menuConstruction = [
            [
                'icon' => 'fa-tachometer-alt',
                'isActive' => false,
                'name' => 'Dashboard',
                'destination' => '/dashboard',
                'children' => [],
                'isHeading' => false,
                'isDivider' => false,
            ],
            [
                'icon' => 'fa-cloud-sun',
                'isActive' => false,
                'name' => 'Weather',
                'destination' => '/weather',
                'children' => [],
                'isHeading' => false,
                'isDivider' => false,
            ],
            [
                'icon' => 'fa-wifi',
                'isActive' => false,
                'name' => 'Network',
                'destination' => '/network',
                'children' => [
                    [], //TODO: deeper
                    [],
                    [],
                ],
                'isHeading' => false,
                'isDivider' => false,
            ],
        ];

        /**
         * @var SideMenuItem $val
         */
        foreach ($menu as $key => $val) {
            $this->assertEquals(
                expected: SideMenuItem::class,
                actual: get_class($val),
                message: '---!---> Wrong menu item class',
            );

            $this->assertEquals(
                expected: $menuConstruction[$key]['icon'],
                actual: $val->getIcon(),
                message: '---!---> Wrong menu item icon',
            );

            $this->assertEquals(
                expected: $menuConstruction[$key]['isActive'],
                actual: $val->getIsActive(),
                message: '---!---> Wrong menu item activity',
            );

            $this->assertEquals(
                expected: $menuConstruction[$key]['name'],
                actual: $val->getName(),
                message: '---!---> Wrong menu item name',
            );

            $this->assertEquals(
                expected: $menuConstruction[$key]['destination'],
                actual: $val->getDestination(),
                message: '---!---> Wrong menu item destination',
            );

            $this->assertEquals(
                expected: $menuConstruction[$key]['isHeading'],
                actual: $val->getIsHeading(),
                message: '---!---> Wrong menu item heading flag',
            );

            $this->assertEquals(
                expected: $menuConstruction[$key]['isDivider'],
                actual: $val->getIsDivider(),
                message: '---!---> Wrong menu item divider flag',
            );

            $this->assertEquals(
                expected: sizeof($menuConstruction[$key]['children']),
                actual: sizeof($val->getChildren()),
                message: '---!---> Wrong children count in ' . $val->getName(),
            );

            foreach ($val->getChildren() as $childKey => $childVal) {
                $this->assertEquals(
                    expected: SideMenuItem::class,
                    actual: get_class($childVal),
                    message: '---!---> Wrong sub-menu item class',
                );
            }
        }
    }
}
