<?php

use PHPUnit\Framework\TestCase;

class SpecialitiesControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        // Tạo mock cho SpecialitiesController và chỉ mock hàm getVariable
        $this->controller = $this->getMockBuilder(SpecialitiesController::class)
            ->onlyMethods(['getVariable', 'getAll', 'save'])
            ->getMock();
    }

    /**
     * TestCase TC001
     * Kiểm tra khi không có AuthUser -> phải redirect về login và exit
     */
    public function testRedirectToLoginIfNotAuthenticated()
    {
        $this->controller->method('getVariable')->with('AuthUser')->willReturn(null);

        $this->expectOutputRegex('/^$/'); // vì có header và exit nên không có output

        // Gọi process và expect là sẽ exit
        $this->expectException(\PHPUnit\Framework\Error\Warning::class); // giả lập exit
        $this->controller->process();
    }

    /**
     * TestCase TC002
     * Kiểm tra nhánh GET khi đã đăng nhập
     */
    public function testProcessCallsGetAllOnGetRequest()
    {
        $AuthUserMock = $this->createMock(stdClass::class);
        $this->controller->method('getVariable')->willReturnMap([
            ['AuthUser', $AuthUserMock],
            ['Route', null]
        ]);

        // Mock Input::method() trả về GET
        Input::shouldReceive('method')->once()->andReturn('GET');

        $this->controller->expects($this->once())->method('getAll');

        $this->controller->process();
    }

    /**
     * TestCase TC003
     * Kiểm tra nhánh POST khi đã đăng nhập
     */
    public function testProcessCallsSaveOnPostRequest()
    {
        $AuthUserMock = $this->createMock(stdClass::class);
        $this->controller->method('getVariable')->willReturnMap([
            ['AuthUser', $AuthUserMock],
            ['Route', null]
        ]);

        Input::shouldReceive('method')->once()->andReturn('POST');

        $this->controller->expects($this->once())->method('save');

        $this->controller->process();
    }

    /**
     * TestCase TC004
     * Kiểm tra nếu request method khác GET/POST thì không gọi gì
     */
    public function testProcessDoesNothingOnInvalidRequestMethod()
    {
        $AuthUserMock = $this->createMock(stdClass::class);
        $this->controller->method('getVariable')->willReturnMap([
            ['AuthUser', $AuthUserMock],
            ['Route', null]
        ]);

        Input::shouldReceive('method')->once()->andReturn('PUT');

        $this->controller->expects($this->never())->method('getAll');
        $this->controller->expects($this->never())->method('save');

        $this->controller->process();
    }
}
