<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;


require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/core/Controller.php';
require_once __DIR__ . '/../../../api/app/controllers/DoctorController.php';
require_once __DIR__ . '/../../../api/app/models/DoctorModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';

class DoctorModelTest extends TestCase
{
    protected static $db;
    protected static $qb;
    protected $doctorModel;

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

    public function tearDown(){
        // Rollback transaction sau mỗi test case
        // self::$db->getPdoInstance()->commit;
        self::$db->getPdoInstance()->rollback();
    }

    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();
        $this->doctorModel = new DoctorModel();
    }

    // m10_DoctorModel_select_01
    // Id hop le
    // Input: id = 37
    // Output: doctorModel với id = 37
    public function testSelectWithValidId()
    {
        // Insert dữ liệu giả lập vào database để test
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 37,
            'email' => 'doctor@example.com',
            'phone' => '1234567890'
        ]);

        // Gọi phương thức select() của DoctorModel
        $doctor = $this->doctorModel->select(37);

        // Kiểm tra kết quả
        // $this->assertTrue($doctor->get('is_available'));
        $this->assertEquals(37, $doctor->get('id'));
        $this->assertEquals('doctor@example.com', $doctor->get('email'));
        $this->assertEquals('1234567890', $doctor->get('phone'));
    }

    // m10_DoctorModel_select_02
    // Test method select() với email hợp lệ
    // Input: email = 'doctor@example.com'
    // Output: doctorModel với id = 37
    public function testSelectWithValidEmail()
    {
        // Insert dữ liệu giả lập vào database
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 37,
            'email' => 'doctor@example.com',
            'phone' => '1234567890'
        ]);

        // Gọi phương thức select() với email
        $doctor = $this->doctorModel->select('doctor@example.com');

        // Kiểm tra kết quả
        // $this->assertTrue($doctor->get('is_available'));
        $this->assertEquals('doctor@example.com', $doctor->get('email'));
    }

    // m10_DoctorModel_select_03
    // Test method select() với số điện thoại hợp lệ
    // Input: phone = '1234567890'
    // Output: doctorModel với id = 37
    public function testSelectWithPhoneNumber()
    {
        // Insert dữ liệu giả lập vào database
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 37,
            'email' => 'doctor@example.com',
            'phone' => '1234567890'
        ]);

        // Gọi phương thức select() với số điện thoại
        $doctor = $this->doctorModel->select('1234567890');
        // fwrite(STDERR, "Hello".var_export($doctor, true));
        // Kiểm tra kết quả
        // $this->assertTrue($doctor->get('is_available'));
        $this->assertEquals('1234567890', $doctor->get('phone'));
    }

    // m10_DoctorModel_select_04
    // Test method select() với id không hợp lệ
    // Input: id = 999
    // Output: is_available = false
    public function testSelectWithInvalidId()
    {
        // Gọi phương thức select() với id không tồn tại
        $doctor = $this->doctorModel->select(999);
        // fwrite(STDERR, var_export($doctor, true));
        // fwrite(STDERR, "\n\n", true);
        // Kiểm tra kết quả khi không tìm thấy dữ liệu
        $this->assertFalse($doctor->get('is_available'));
    }

    // m10_DoctorModel_update_01
    // Test method update() với id hợp lệ
    // Input: id = 36
    // Output: doctorModel với id = 36
    public function testUpdateDoctorWithValidID()
    {
        
        // Arrange
        $doctor = new DoctorModel(36);
        $this->assertTrue($doctor->isAvailable(), "Doctor with ID 36 does not exist!");

        $newName = "Test Doctor Transaction hihii";
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
        $dbDoctor = self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->where("id", "=", 36)->get();
        $this->assertEquals($newName, $dbDoctor[0]->name);
        $this->assertEquals($newDescription, $dbDoctor[0]->description);

    }

   
   
}
