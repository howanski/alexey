<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Class\SideMenuItem;
use PHPUnit\Framework\TestCase;
use App\Service\AlexeySideMenuProvider;
use App\Service\AlexeyTranslator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class AlexeySideMenuProviderTest extends TestCase
{
    public function testExportMenuSchema(): void
    {
        $requestStack = $this->createMock(originalClassName: RequestStack::class);
        $router = $this->createMock(originalClassName: RouterInterface::class);
        $translator = $this->createMock(originalClassName: AlexeyTranslator::class);

        $routerCallback = function (string $path) {
            return '/' . $path;
        };

        $router->method('generate')->willReturnCallback($routerCallback);

        $transCallback = function (string $string) {
            return 'trans_' . $string;
        };

        $translator->method('translateString')->willReturnCallback($transCallback);


        $service = new AlexeySideMenuProvider(
            requestStack: $requestStack,
            router: $router,
            translator: $translator,
        );
        $menu = $service->exportMenuSchema();

        $this->assertIsArray(
            actual: $menu,
            message: '---!---> Wrong menu construction',
        );

        $this->assertEquals(
            expected: 4,
            actual: sizeof($menu),
            message: '---!---> Main menu items count changed',
        );

        $menuConstruction = [
            [
                'icon' => 'fa-yin-yang',
                'isActive' => false,
                'name' => 'trans_menu_record',
                'destination' => '/dashboard',
                'children' => [],
            ],
            [
                'icon' => 'fa-cloud-sun',
                'isActive' => false,
                'name' => 'trans_menu_record',
                'destination' => '/weather',
                'children' => [],
            ],
            [
                'icon' => 'fa-wifi',
                'isActive' => false,
                'name' => 'trans_menu_record',
                'destination' => '/network',
                'children' => [
                    [], //TODO: deeper
                    [],
                    [],
                ],
            ],
            [
                'icon' => 'fas fa-search-dollar',
                'isActive' => false,
                'name' => 'trans_menu_record',
                'destination' => '/money',
                'children' => [
                    [], //TODO: deeper
                    [],
                    [],
                ],
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
