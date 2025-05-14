<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;


class LoginByPatientControllerTest extends TestCase
{
    protected static $db;
    protected static $qb;

    private $controller;
    private $routeMock;

    public static function setUpBeforeClass(): void
    {
        $config = require __DIR__ . '/../../LocalConfigDB.php';
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function tearDown(): void
    {
        self::$db->getPdoInstance()->rollback();
    }

    public function setUp(): void
    {
        self::$db->getPdoInstance()->beginTransaction();

        $this->controller = new LoginHelperController();
        $this->routeMock = $this->createMock(stdClass::class);
        $this->controller->mockRoute = $this->routeMock;
    }

    private function callLoginByPatient($controller)
    {
        $reflection = new \ReflectionClass(LoginController::class); 
        $method = $reflection->getMethod('loginByPatient');
        $method->setAccessible(true);

        try {
            $method->invoke($controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') {
                throw $e;
            }
        }
    }

    public function test_login_new_patient_success()
    {
        $phone = '0912345678';
        self::$qb->table('tn_patients')->where('phone', $phone)->delete();

        $_POST['phone'] = $phone;
        $_POST['password'] = 'password123';

        $this->callLoginByPatient($this->controller);

        $response = json_decode($this->controller->output, true);

        $this->assertEquals(1, $response['result']);
        $this->assertEquals("Welcome to UMBRELLA CORPORATION, $phone !", $response['msg']);
        $this->assertArrayHasKey('accessToken', $response);
        $this->assertEquals($phone, $response['data']['phone']);
        $this->assertEquals(0, $response['data']['gender']);
    }

    public function test_login_existing_patient_success()
    {
        self::$qb->table('tn_patients')->where('phone', '0999999999')->delete();
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

        $this->callLoginByPatient($this->controller);

        $response = json_decode($this->controller->output, true);

        $this->assertEquals(1, $response['result']);
        $this->assertEquals("Welcome back to UMBRELLA CORPORATION, Test User !", $response['msg']);
        $this->assertEquals("0999999999", $response['data']['phone']);
    }

    public function test_phone_empty_should_return_error()
    {
        $_POST['phone'] = '';
        $_POST['password'] = 'abc123';

        $this->callLoginByPatient($this->controller);

        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals('Phone number can not be empty !', $response['msg']);
    }

    public function test_phone_less_than_10_characters_should_return_error()
    {
        $_POST['phone'] = '12345';
        $_POST['password'] = 'abc123';

        $this->callLoginByPatient($this->controller);

        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals('Phone number has at least 10 number !', $response['msg']);
    }

    public function test_phone_not_numeric_should_return_error()
    {
        $_POST['phone'] = 'abc123def4';
        $_POST['password'] = 'abc123';

        $this->callLoginByPatient($this->controller);

        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals('This is not a valid phone number. Please, try again !', $response['msg']);
    }
}
