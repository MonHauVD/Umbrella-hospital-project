<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

if (!defined('APPPATH')) {
    define('APPPATH', realpath(__DIR__ . '/../../api/app'));
}
if (!defined('EC_SALT')) {
    define('EC_SALT', 'your_test_secret_key_here');
}

require_once __DIR__ . '/../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../api/app/core/Controller.php';
require_once __DIR__ . '/../../api/app/core/Input.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';
require_once __DIR__ . '/../../api/app/core/DataList.php';
require_once __DIR__ . '/../../api/app/helpers/common.helper.php';
require_once __DIR__ . '/../../api/app/controllers/LoginController.php';
require_once __DIR__ . '/../../tests/Module_03/helper/TestableHelperController.php';
require_once __DIR__ . '/../../tests/Module_03/helper/FakeAuthUser.php';

class LoginByPatientControllerTest extends TestCase
{
    protected static $db;
    protected static $qb;

    private $controller;
    private $routeMock;
    private $authMock;
    public static function setUpBeforeClass(): void
    {
        // Khởi tạo Pixie Connection
        $config = require __DIR__ . '/../../LocalConfigDB.php';
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function tearDown(): void{
        // Rollback transaction sau mỗi test case
        self::$db->getPdoInstance()->rollback();
    }

    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();

         // Create an instance of the controller
        $this->controller = $this->getMockBuilder(LoginController::class)
                                  ->setMethods(['getVariable', 'jsonecho']) // Mocking getVariable
                                  ->getMock();


         // Create a mock for the Route class (assuming it's a class)
         $this->authMock = $this->createMock(stdClass::class);
         $this->routeMock = $this->createMock(stdClass::class);
 
         // Mock the getVariable method to return the routeMock
        //  $this->controller->method('getVariable')->willReturn($this->routeMock);
        $this->controller->method('getVariable')
                 ->will($this->returnCallback(function($name) {
                     if ($name === "Route") {
                         return $this->routeMock;
                     } elseif ($name === "AuthUser") {
                         return $this->authMock;
                     }
                     return null;
                 }));
                 
        //Stop code when calling jsonecho method
        // $this->controller->method('jsonecho')->will($this->returnCallback(function() {
        //     return; // Ngừng thực thi phần còn lại của hàm
        // }));
        // Cho phép test đọc được resp thông qua Reflection
        $controller = $this->controller;
        $this->controller->method('jsonecho')->will($this->returnCallback(function () use ($controller) {
            $reflection = new ReflectionClass($controller);
            $prop = $reflection->getProperty('resp');
            $prop->setAccessible(true);
            echo json_encode($prop->getValue($controller));
        }));
    }
    


    //Regiser
    public function test_login_new_patient_success()
    {
        $phone = '0912345678';

        // Đảm bảo xóa dữ liệu cũ nếu tồn tại (tránh fail do đã tồn tại)
        self::$qb->table('tn_patients')->where('phone', $phone)->delete();

        // Giả lập POST
        $_POST['phone'] = $phone;
        $_POST['password'] = 'password123';

        // Nếu Input::post() được dùng thì nên bật mock
        // Input::mock(['phone' => $phone, 'password' => 'password123']);

        ob_start();
        $this->controller->loginByPatient();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        // Kiểm tra response
        $this->assertEquals(1, $response['result']);
        $this->assertEquals("Welcome to UMBRELLA CORPORATION, $phone !", $response['msg']);
        $this->assertArrayHasKey('accessToken', $response);
        $this->assertEquals($phone, $response['data']['phone']);
        $this->assertEquals(0, $response['data']['gender']);
    }

    //login
    public function test_login_existing_patient_success()
    {
        // Insert bệnh nhân trước (fake data)
        self::$qb->table('tn_patients')->insert([
            'phone' => '0999999999',
            'password' => password_hash('abc123', PASSWORD_DEFAULT),
            'name' => 'Test User',
            'email' => '',
            'gender' => 1,
            'birthday' => '',
            'address' => '',
            'avatar' => '',
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);

        $_POST['phone'] = '0999999999';
        $_POST['password'] = 'abc123';

        ob_start();
        $this->controller->loginByPatient();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(1, $response['result']);
        $this->assertEquals("Welcome back to UMBRELLA CORPORATION, Test User !", $response['msg']);
        $this->assertEquals("0999999999", $response['data']['phone']);
    }

    //số điện thoại rỗng
    public function test_phone_empty_should_return_error()
    {
        $controller = new LoginHelperController();

        $_POST['phone'] = ''; // để trống
        $_POST['password'] = 'abc123';

        try {
            $controller->loginByPatient();
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') {
                throw $e; // chỉ bỏ qua exception giả lập exit
            }
        }

        $response = json_decode($controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals('Phone number can not be empty !', $response['msg']);
    }

    //Số điện thoại < 10 ký tự
    public function test_phone_less_than_10_characters_should_return_error()
    {
        $controller = new LoginHelperController();

        $_POST['phone'] = '12345'; // < 10 ký tự
        $_POST['password'] = 'abc123';

        try {
            $controller->loginByPatient();
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') {
                throw $e;
            }
        }

        $response = json_decode($controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals('Phone number has at least 10 number !', $response['msg']);
    }

    //Số điện thoại không hợp lệ (chứa ký tự)
    public function test_phone_not_numeric_should_return_error()
    {
        $controller = new LoginHelperController();

        $_POST['phone'] = 'abc123def4'; // không hợp lệ
        $_POST['password'] = 'abc123';

        try {
            $controller->loginByPatient();
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') {
                throw $e;
            }
        }

        $response = json_decode($controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals('This is not a valid phone number. Please, try again !', $response['msg']);
    }




}
