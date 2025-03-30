<?php
// namespace App\Tests\Models;

// use PDO;
use PHPUnit\Framework\TestCase;
// use App\Models\DoctorModel;
use Pixie\Connection;
require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/models/DoctorModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';

class DoctorModelTest2 extends TestCase
{
    protected static $pdo;
    protected $transactionStarted = false;
    
    public static function setUpBeforeClass(): void
    {
        // Kết nối PDO để kiểm tra Transaction
        self::$pdo = new PDO('mysql:host=localhost;dbname=doantotnghiep', 'root', '');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ✅ Khởi tạo Pixie Connection (giống App.php)
        $config = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'doantotnghiep',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ];
        new Connection('mysql', $config, 'DB'); // Tạo class DB
    }

    protected function setUp(): void
    {
        self::$pdo->beginTransaction();
        $this->transactionStarted = true;
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) {
            self::$pdo->rollBack();
        }
    }

    public function testUpdateDoctor()
{
    // Arrange
    $doctor = new DoctorModel(36); // Lấy bác sĩ có id = 5

    // Kiểm tra bác sĩ có tồn tại không
    $this->assertTrue($doctor->isAvailable(), "Doctor with ID does not exist!");

    // Lưu giá trị cũ để rollback
    $oldName = $doctor->get("name");
    $oldDescription = $doctor->get("description");

    // Act
    $doctor->set("name", "Test Doctor Updated");
    $doctor->set("description", "Updated Description");
    $result = $doctor->update();

    // Assert
    $this->assertInstanceOf(DoctorModel::class, $result);
    $this->assertEquals("Test Doctor Updated", $doctor->get("name"));
    $this->assertEquals("Updated Description", $doctor->get("description"));

    // Verify in DB
    $dbDoctor = DB::table(TABLE_PREFIX.TABLE_DOCTORS)->where("id", "=", 36)->get();
    $this->assertEquals("Test Doctor Updated", $dbDoctor[0]->name);
    $this->assertEquals("Updated Description", $dbDoctor[0]->description);

    // Rollback để giữ nguyên dữ liệu sau test
    $doctor->set("name", $oldName);
    $doctor->set("description", $oldDescription);
    $doctor->update();
}
}
