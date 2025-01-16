<?php

declare(strict_types=1);

namespace App\Tests\Class;

use PHPUnit\Framework\TestCase;
use App\Class\ApiResponse;

class ApiResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $locale = 'en_US';
        $response = new ApiResponse($locale);
        $this->assertEquals($locale, $response->getLocale());
    }

    public function testDefaultMessageAndCode(): void
    {
        $response = new ApiResponse('en_US');
        $this->assertEquals('ok', $response->getMessage());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCustomMessageAndCode(): void
    {
        $response = new ApiResponse('en_US');
        $response->setMessage('Hello!');
        $response->setCode(201);
        $this->assertEquals('Hello!', $response->getMessage());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testAddingElementsToUi(): void
    {
        $response = new ApiResponse('en_US');
        $response->addText('Test Text');
        $response->addSpacer();
        $response->addHorizontalLine();
        $response->addButton('Click Me', '/path/to/button');
        $response->addLink('Visit Us', '/our-site');

        // Verify UI content
        $expectedUi = [
            ['type' => ApiResponse::UI_ELEMENT_TEXT, 'value' => 'Test Text'],
            [
                'type' => ApiResponse::UI_ELEMENT_TEXT,
                'value' => '                              ',
            ],
            ['type' => ApiResponse::UI_ELEMENT_TEXT, 'value' => '⎯⎯⎯⎯⎯⎯⎯⎯⎯⎯⎯⎯'],
            ['type' => ApiResponse::UI_ELEMENT_BUTTON, 'name' => 'Click Me', 'path' => '/path/to/button'],
            ['type' => ApiResponse::UI_ELEMENT_LINK, 'name' => 'Visit Us', 'path' => '/our-site'],
        ];
        $this->assertEquals($expectedUi, $response->getUiContent());
    }

    public function testAutoRefresh(): void
    {
        $response = new ApiResponse('en_US');
        $response->setRefreshInSeconds(5);
        $this->assertEquals(5000, $response->getRefreshTime());
    }

    public function testToResponse(): void
    {
        $response = new ApiResponse('en_US');
        $response->setMessage('Test Message');
        $response->setCode(201);
        $response->addText('Some Text');
        $jsonResponse = $response->toResponse();

        // Extract expected data
        $expectedData = json_encode([
            'code' => 201,
            'message' => 'Test Message',
            'autoRefresh' => 0, // Assuming no refresh is set
            'ui' => [
                ['type' => ApiResponse::UI_ELEMENT_TEXT, 'value' => 'Some Text'],
            ],
            'loc' => 'en_US',
        ]);
        $this->assertEquals($expectedData, $jsonResponse->getContent());
    }
}
