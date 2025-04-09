<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../api/app/models/BookingModel.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';

class BookingModelTest extends TestCase
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
            'password'  => '123456',
            'charset'   => 'utf8',
            'options'   => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ];
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }
    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();
    }

    public function tearDown(){
        // Rollback transaction sau mỗi test case
        self::$db->getPdoInstance()->rollback();
    }


    // Test case lấy thông tin booking từ database với một id tồn tại
    public function test_M07_BookingModel_select_01()
    {
        $ids = 112;
        // Arrange
        $booking = new BookingModel($ids);
    
        $this->assertEquals( "112", $booking->get("id"));
        $this->assertEquals( "PhongKaster", $booking->get("booking_name"));
        $this->assertEquals("0366964346", $booking->get("booking_phone"));
        $this->assertEquals( "0", $booking->get("doctor_id"));
        $this->assertEquals("38", $booking->get("patient_id"));
        $this->assertEquals( "24", $booking->get("service_id"));
        $this->assertEquals("PhongKaster", $booking->get("name"));
        $this->assertEquals( "2025-03-19 16:51:57", $booking->get("update_at"));
        $this->assertEquals("2025-03-19 16:51:57", $booking->get("create_at"));

        // Verify in DB
        $dbBooking = DB::table(TABLE_PREFIX.TABLE_BOOKINGS)->where("id", "=", $ids)->get();
        $this->assertEquals(1, count($dbBooking));
        $this->assertEquals( $dbBooking[0]->id, $booking->get("id"));
        $this->assertEquals( $dbBooking[0]->booking_name, $booking->get("booking_name"));
        $this->assertEquals($dbBooking[0]->booking_phone, $booking->get("booking_phone"));
    }

    // Test case lấy thông tin booking từ database với một id tồn tại
    public function test_M07_BookingModel_select_02()
    {
        $ids = 999;
        // Arrange
        $booking = new BookingModel($ids);
    
        $this->assertFalse( $booking->isAvailable());
        $this->assertNull($booking->get("id"));
    }

    // Test case lấy thông tin booking từ database id không hợp lệ
    public function test_M07_BookingModel_select_03()
    {
        $ids = "fdsfbdng";
        // Arrange
        $booking = new BookingModel($ids);
    
        $this->assertFalse( $booking->isAvailable());
        $this->assertNull($booking->get("id"));
    }

    // Test case lấy thông tin booking từ database với id truyền vào là mã sql injection
    public function test_M07_BookingModel_select_04()
    {
        $ids = "' OR '1'='1' --";
        // Arrange
        $booking = new BookingModel($ids);
        $this->assertFalse( $booking->isAvailable());
        $this->assertNull($booking->get("id"));
    }


    public function test_M07_BookingModel_insert_01()
    {
        // Arrange
        $booking = new BookingModel();
        $this->assertFalse( $booking->isAvailable());
        $booking->set("service_id", "24");
        $booking->set("doctor_id", "0");
        $booking->set("patient_id", "38");
        $booking->set("booking_name", "PhongKaster");
        $booking->set("booking_phone", "0366964346");
        $booking->set("name", "PhongKaster");
        $booking->set("gender", "1");
        $booking->set("birthday", "2024-12-12");
        $booking->set("address", "2024-12-12");
        $booking->set("reason", "Deadline di cang qua");
        $booking->set("appointment_date", "2025-03-19");
        $booking->set("status", "processing");
        $booking->set("create_at", "2025-03-19 16:51:57");
        $booking->set("update_at", "2025-03-19 16:51:57");

        $booking->save();
        // Assert
        $this->assertTrue( $booking->isAvailable());
        $this->assertNotNull( $booking->get("id"));
        // Verify in DB
        $fetchBooking = new BookingModel($booking->get("id"));
        $this->assertEquals( $booking->get("id"), $fetchBooking->get("id"));
        $this->assertEquals( $booking->get("service_id"), $fetchBooking->get("service_id"));
        $this->assertEquals( $booking->get("doctor_id"), $fetchBooking->get("doctor_id"));

    }

    public function test_M07_BookingModel_insert_02_MissingFields()
{
    $this->expectException(PDOException::class);
    // Arrange
    $booking = new BookingModel();
    // Missing required fields
    $booking->set("service_id", "24");
    $booking->set("doctor_id", "0");
    // Act
    $booking->save();
    // Assert
    // $this->assertFalse($booking->isAvailable(), "Booking should not be available when required fields are missing");
}

    // Test case kiểm tra việc thêm một booking với các trường không hợp lệ
    public function test_M07_BookingModel_insert_03_invalidFields()
    {
        $this->expectException(PDOException::class);
        // Arrange
        $booking = new BookingModel();
        $this->assertFalse( $booking->isAvailable());
        $booking->set("service_id", "invalid");     // Invalid service_id   
        $booking->set("doctor_id", "invalid");            // Invalid doctor_id
        $booking->set("patient_id", "38");
        $booking->set("booking_name", "PhongKaster");
        $booking->set("booking_phone", "0366964346");
        $booking->set("name", "PhongKaster");
        $booking->set("gender", "1");
        $booking->set("birthday", "2024-12-12");
        $booking->set("address", "2024-12-12");
        $booking->set("reason", "Deadline di cang qua");
        $booking->set("appointment_date", "2025-03-19");
        $booking->set("status", "processing");
        $booking->set("create_at", "2025-03-19 16:51:57");
        $booking->set("update_at", "2025-03-19 16:51:57");
        // Act
        $booking->save();
    
        // Assert
        // Verify in DB
    }

    // Test case lấy thông tin booking từ database với một service_id không tồn tại 
    public function test_M07_BookingModel_insert_04_serviceIdNotExists()
    {
        $this->expectException(PDOException::class);
        // Arrange
        $booking = new BookingModel();
        $this->assertFalse( $booking->isAvailable());
        $booking->set("service_id", "25");      //service_id không tồn tại 
        $booking->set("doctor_id", "0");
        $booking->set("patient_id", "38");
        $booking->set("booking_name", "PhongKaster");
        $booking->set("booking_phone", "0366964346");
        $booking->set("name", "PhongKaster");
        $booking->set("gender", "1");
        $booking->set("birthday", "2024-12-12");
        $booking->set("address", "2024-12-12");
        $booking->set("reason", "Deadline di cang qua");
        $booking->set("appointment_date", "2025-03-19");
        $booking->set("status", "processing");
        $booking->set("create_at", "2025-03-19 16:51:57");
        $booking->set("update_at", "2025-03-19 16:51:57");

        $booking->save();
        // Assert
    }


     // Test case lấy thông tin booking từ database với một doctor_id không tồn tại 
     public function test_M07_BookingModel_insert_05_doctorIdNotExists()
     {
         $this->expectException(PDOException::class);
         // Arrange
         $booking = new BookingModel();
         $this->assertFalse( $booking->isAvailable());
         $booking->set("service_id", "24");       
         $booking->set("doctor_id", "1000");          // doctor_id không tồn tại
         $booking->set("patient_id", "38");
         $booking->set("booking_name", "PhongKaster");
         $booking->set("booking_phone", "0366964346");
         $booking->set("name", "PhongKaster");
         $booking->set("gender", "1");
         $booking->set("birthday", "2024-12-12");
         $booking->set("address", "2024-12-12");
         $booking->set("reason", "Deadline di cang qua");
         $booking->set("appointment_date", "2025-03-19");
         $booking->set("status", "processing");
         $booking->set("create_at", "2025-03-19 16:51:57");
         $booking->set("update_at", "2025-03-19 16:51:57");
 
         $booking->save();
         // Assert
     }

    // Test case lấy thông tin booking từ database với một patient_id không tồn tại 
    public function test_M07_BookingModel_insert_06_patientIdNotExists()
    {
        $this->expectException(PDOException::class);
        // Arrange
        $booking = new BookingModel();
        $this->assertFalse( $booking->isAvailable());
        $booking->set("service_id", "24");       
        $booking->set("doctor_id", "38");           
        $booking->set("patient_id", "1000");        // patient_id không tồn tại
        $booking->set("booking_name", "PhongKaster");
        $booking->set("booking_phone", "0366964346");
        $booking->set("name", "PhongKaster");
        $booking->set("gender", "1");
        $booking->set("birthday", "2024-12-12");
        $booking->set("address", "2024-12-12");
        $booking->set("reason", "Deadline di cang qua");
        $booking->set("appointment_date", "2025-03-19");
        $booking->set("status", "processing");
        $booking->set("create_at", "2025-03-19 16:51:57");
        $booking->set("update_at", "2025-03-19 16:51:57");

        $booking->save();
        // Assert
    }

     // Test case lấy thông tin booking từ database với một gender không tồn tại 
     public function test_M07_BookingModel_insert_07_invalidGender()
     {
         $this->expectException(PDOException::class);
         // Arrange
         $booking = new BookingModel();
         $this->assertFalse( $booking->isAvailable());
         $booking->set("service_id", "24");       
         $booking->set("doctor_id", "38");           
         $booking->set("patient_id", "40");        
         $booking->set("booking_name", "PhongKaster");
         $booking->set("booking_phone", "0366964346");
         $booking->set("name", "PhongKaster");
         $booking->set("gender", "30000");                  // invalid gender
         $booking->set("birthday", "2024-12-12");
         $booking->set("address", "2024-12-12");
         $booking->set("reason", "Deadline di cang qua");
         $booking->set("appointment_date", "2025-03-19");
         $booking->set("status", "processing");
         $booking->set("create_at", "2025-03-19 16:51:57");
         $booking->set("update_at", "2025-03-19 16:51:57");
 
         $booking->save();
         // Assert
        //  $this->assertTrue( $booking->isAvailable());
        //  $this->assertEquals( "30000", $booking->get("gender"));
     }


     // Kiểm tra nhánh returnn false được thực thi hay không
     public function test_M07_BookingModel_update_01(){
        $booking = new BookingModel();
        $res = $booking->update();
        $this->assertFalse($res);
     }
     

     //Mục tiêu: kiểm tra nhánh isAvailable == true, và kiểm tra kết quả return là $this
     public function test_M07_BookingModel_update_02()
    {
        
        $booking = new BookingModel(112);

        

        $booking->method('isAvailable')->willReturn(true);
        $booking->expects($this->once())->method('extendDefaults');

        // Giả lập get() trả về dữ liệu cụ thể cho từng field
        $booking->method('get')->willReturnMap([
            ['id', 1],
            ['doctor_id', 10],
            ['patient_id', 20],
            ['service_id', 5],
            ['booking_name', 'John Doe'],
            ['booking_phone', '123456789'],
            ['name', 'John'],
            ['gender', 'Male'],
            ['birthday', '1990-01-01'],
            ['address', '123 Test St'],
            ['reason', 'Flu'],
            ['appointment_date', '2025-04-10'],
            ['appointment_time', '10:00'],
            ['status', 'pending'],
            ['create_at', '2025-04-01 10:00:00'],
            ['update_at', '2025-04-01 10:10:00']
        ]);

        // Giả lập update query (tránh gọi DB thật)
        DB::shouldReceive('table')
            ->once()
            ->with(TABLE_PREFIX.TABLE_BOOKINGS)
            ->andReturnSelf();
        
        DB::shouldReceive('where')
            ->once()
            ->with('id', '=', 1)
            ->andReturnSelf();
        
        DB::shouldReceive('update')
            ->once()
            ->andReturn(1); // giả lập update thành công

        $result = $booking->update();

        $this->assertSame($booking, $result);
    }


}