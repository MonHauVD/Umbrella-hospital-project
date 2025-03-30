<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/core/Controller.php';
require_once __DIR__ . '/../../../api/app/controllers/DoctorController.php';
require_once __DIR__ . '/../../../api/app/models/DoctorModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';

class DoctorModelTest4 extends TestCase
{
    protected static $db;
    protected static $qb;

    public static function setUpBeforeClass(): void
    {
        // Khởi tạo Pixie Connection
        $config = [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'doantotnghiep',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'options'   => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ];
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function testUpdateDoctor()
    {
        DB::transaction(function ($qb) {
            // Arrange
            $doctor = new DoctorModel(36);
            $this->assertTrue($doctor->isAvailable(), "Doctor with ID 36 does not exist!");

            $newName = "Test Doctor Transaction huhu";
            $newDescription = "Description Transaction";

            // Act
            $doctor->set("name", $newName);
            $doctor->set("description", $newDescription);
            $result = $doctor->update();

            // Assert
            $this->assertInstanceOf(DoctorModel::class, $result);
            $this->assertEquals($newName, $doctor->get("name"));
            $this->assertEquals($newDescription, $doctor->get("description"));

            // Verify in DB
            $dbDoctor = $qb->table(TABLE_PREFIX . TABLE_DOCTORS)->where("id", "=", 36)->get();
            $this->assertEquals($newName, $dbDoctor[0]->name);
            $this->assertEquals($newDescription, $dbDoctor[0]->description);

            $qb->rollback();
        });
    }

    /** --------------------------- UNIT TEST getById -------------------------- */

    /** @test */
    public function testGetById_missing_id()
{
    $controller = $this->getMockBuilder(DoctorController::class)
                       ->disableOriginalConstructor()
                       ->setMethods(['getVariable', 'jsonecho'])
                       ->getMock();

    $controller->method('getVariable')
               ->willReturnMap([
                   ['AuthUser', (object)['role' => 'admin']],
                   ['Route', (object)['params' => (object)[]]] // Không có id
               ]);

    // Giả lập jsonecho
    $controller->expects($this->once())
               ->method('jsonecho')
               ->willReturn(null);

    // Sử dụng Reflection để gọi private function
    $method = new ReflectionMethod(DoctorController::class, 'getById');
    $method->setAccessible(true);
    $method->invoke($controller);

    // Lấy biến protected resp ra để kiểm tra
    $reflection = new ReflectionClass($controller);
    $property = $reflection->getProperty('resp');
    $property->setAccessible(true);
    $resp = $property->getValue($controller);

    // Assert kết quả
    $this->assertEquals(0, $resp->result);
    $this->assertEquals("ID is required !", $resp->msg);
}

    /** @test */
    public function testGetById_doctor_not_found()
    {
        $controller = $this->getMockBuilder(DoctorController::class)
                           ->disableOriginalConstructor()
                           ->setMethods(['getVariable', 'jsonecho'])
                           ->getMock();

        $controller->resp = new stdClass();

        $routeMock = new stdClass();
        $routeMock->params = (object)['id' => 999999]; // ID không tồn tại

        $controller->method('getVariable')
                   ->with('Route')
                   ->willReturn($routeMock);

        $controller->expects($this->once())
                   ->method('jsonecho')
                   ->willReturnCallback(function () use ($controller) {
                       $this->assertEquals("Doctor is not available", $controller->resp->msg);
                   });

        $this->invokeMethod($controller, 'getById');
    }

    // Test trường hợp lấy dữ liệu thành công
    // Input: 2
    // Expection Output: doctor có id = 2
    public function testGetById_success()
    {
        $doctorId = 2; // ID giả định có tồn tại

        // Mock controller
        $controller = $this->getMockBuilder(DoctorController::class)
                        ->disableOriginalConstructor()
                        ->setMethods(['getVariable', 'jsonecho'])
                        ->getMock();

        // Giả lập getVariable trả về AuthUser và Route có id
        $controller->method('getVariable')
                ->willReturnMap([
                    ['AuthUser', (object)['role' => 'admin']],
                    ['Route', (object)['params' => (object)['id' => $doctorId]]]
                ]);

        // Giả lập jsonecho
        $controller->expects($this->once())
                ->method('jsonecho')
                ->willReturn(null);

        // Tạo giả dữ liệu doctor
        DB::table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => $doctorId,
            'email' => 'test@example.com',
            'phone' => '123456789',
            'name' => 'Dr. Test',
            'description' => 'Test Description',
            'price' => 100,
            'role' => 'doctor',
            'avatar' => 'avatar.jpg',
            'active' => 1,
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s'),
            'speciality_id' => null,
            'room_id' => null
        ]);

        // --- Gọi private function ---
        $method = new ReflectionMethod(DoctorController::class, 'getById');
        $method->setAccessible(true);
        $method->invoke($controller);

        // --- Kiểm tra dữ liệu trả về ---
        $reflection = new ReflectionClass($controller);
        $property = $reflection->getProperty('resp');
        $property->setAccessible(true);
        $resp = $property->getValue($controller);

        // --- Assert ---
        $this->assertEquals(1, $resp->result);
        $this->assertEquals("Action successfully !", $resp->msg);
        $this->assertNotEmpty($resp->data);
        $this->assertEquals($doctorId, $resp->data['id']);
        $this->assertEquals('Dr. Test', $resp->data['name']);
        $this->assertEquals('Test Description', $resp->data['description']);

        // Rollback thủ công (nếu muốn)
        DB::table(TABLE_PREFIX . TABLE_DOCTORS)->where('id', '=', $doctorId)->delete();
    }


    /** ------------------ Helper để gọi private function ----------------- */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
