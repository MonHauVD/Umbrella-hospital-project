<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../api/app/models/PatientModel.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';

class PatientModelTest extends TestCase
{
    protected static $db;
    protected static $qb;

    public static function setUpBeforeClass(): void
    {
        // Khởi tạo Pixie Connection
        $config =  require __DIR__ . '/../../LocalConfigDB.php';
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
    public function test_M11_PatientModel_select_01()
    {
        $ids = 1;
        // Arrange
        $booking = new PatientModel($ids);
    
        $this->assertTrue( $booking->isAvailable());
        $this->assertEquals( "1", $booking->get("id"));
        $this->assertEquals("0123456789", $booking->get("phone"));
        $this->assertEquals( "Tiểu Long Nữ", $booking->get("name"));
        $this->assertEquals( "2022-12-22 17:04:31", $booking->get("update_at"));
        $this->assertEquals("2022-11-04 16:37:27", $booking->get("create_at"));

        // Verify in DB
        $dbBooking = DB::table(TABLE_PREFIX.TABLE_PATIENTS)->where("id", "=", $ids)->get();
        $this->assertEquals(1, count($dbBooking));
        $this->assertEquals( $dbBooking[0]->id, $booking->get("id"));
        $this->assertEquals( $dbBooking[0]->phone, $booking->get("phone"));
        $this->assertEquals($dbBooking[0]->name, $booking->get("name"));
    }

    // Test case lấy thông tin booking từ database với một id không có trong db
    public function test_M11_PatientModel_select_02()
    {
        $ids = 999;
        // Arrange
        $booking = new PatientModel($ids);
    
        $this->assertFalse( $booking->isAvailable());
        $this->assertNull($booking->get("id"));
    }

    // Test case lấy thông tin booking từ database id không hợp lệ
    public function test_M11_PatientModel_select_03()
    {
        $ids = "fdsfbdndassdassg";
        // Arrange
        $booking = new PatientModel($ids);
    
        $this->assertFalse( $booking->isAvailable());
        $this->assertNull($booking->get("id"));
    }

    // Test case lấy thông tin booking từ database với id truyền vào là mã sql injection
    public function test_M11_PatientModel_select_04()
    {
        $ids = "' OR '1'='1' --";
        // Arrange
        $booking = new PatientModel($ids);
        $this->assertFalse( $booking->isAvailable());
        $this->assertNull($booking->get("id"));
    }


    //Test case insert thành công với các trường hợp hợp lệ
    public function test_M11_PatientModel_insert_01()
    {
        // Arrange
        $booking = new PatientModel();

        $defaults = array(
           "email" => "nguyendat1610175test@gmail.com",
            "phone" => "0366964341",
            "password" => "D@t1610175",
            "name" => "Nguyen Tran Dat",
            "gender" => 1,
            "birthday" => "05/12/2001",
            "address" => "Hà Nội",
            "avatar" => "avatar_2_1670820370.jpg",
            "create_at" => date("Y-m-d H:i:s"),
            "update_at" => date("Y-m-d H:i:s")
        );


        foreach ($defaults as $field => $value) {
            $booking->set($field, $value);
        }

        $this->assertFalse( $booking->isAvailable());

        $booking->save();
        // Assert
        $this->assertTrue( $booking->isAvailable());
        $this->assertNotNull( $booking->get("id"));
        // Verify in DB
        $fetchBooking = new PatientModel($booking->get("id"));
        $this->assertEquals( $booking->get("email"), $fetchBooking->get("email"));
        $this->assertEquals( $booking->get("phone"), $fetchBooking->get("phone"));
        $this->assertNotEquals( $booking->get("password"), $fetchBooking->get("password"));
    }

    // Test case insert không thành công với các trường null
    public function test_M11_PatientModel_insert_02()
    {
        $this->expectException(PDOException::class);
         // Arrange
         $booking = new PatientModel();

         $defaults = array(
            "email" => null,
             "phone" => null,
             "password" => null,
             "name" => "Nguyen Tran Dat",
             "gender" => 1,
             "birthday" => "05/12/2001",
             "address" => "Hà Nội",
             "avatar" => "avatar_2_1670820370.jpg",
             "create_at" => date("Y-m-d H:i:s"),
             "update_at" => date("Y-m-d H:i:s")
         );
 
 
         foreach ($defaults as $field => $value) {
             $booking->set($field, $value);
         }

        $this->assertFalse( $booking->isAvailable());

        $booking->save();
        // Assert
        $this->assertFalse( $booking->isAvailable());
    }

    // Test case insert không thành công với email trùng lặp,
    public function test_M11_PatientModel_insert_03()
    {
        $this->expectException(PDOException::class);
        // Arrange
        $booking = new PatientModel();

        $defaults = array(
           "email" => "phongkaster100@gmail.com",
            "phone" => "",
            "password" => "",
            "name" => "Nguyen Tran Dat",
            "gender" => 1,
            "birthday" => "05/12/2001",
            "address" => "Hà Nội",
            "avatar" => "avatar_2_1670820370.jpg",
            "create_at" => date("Y-m-d H:i:s"),
            "update_at" => date("Y-m-d H:i:s")
        );


        foreach ($defaults as $field => $value) {
            $booking->set($field, $value);
        }

       $this->assertFalse( $booking->isAvailable());

       $booking->save();
       // Assert
       $this->assertFalse( $booking->isAvailable());
    }

    // Test case insert không thành công với số điện thoại lặp, 
    public function test_M11_PatientModel_insert_04()
    {
        $this->expectException(PDOException::class);
        // Arrange
        $booking = new PatientModel();

        $defaults = array(
           "email" => "phongksfdsfdaster100@gmail.com",
            "phone" => "0123456789",
            "password" => "",
            "name" => "Nguyen Tran Dat",
            "gender" => 1,
            "birthday" => "05/12/2001",
            "address" => "Hà Nội",
            "avatar" => "avatar_2_1670820370.jpg",
            "create_at" => date("Y-m-d H:i:s"),
            "update_at" => date("Y-m-d H:i:s")
        );


        foreach ($defaults as $field => $value) {
            $booking->set($field, $value);
        }

       $this->assertFalse( $booking->isAvailable());

       $booking->save();
       // Assert
       $this->assertFalse( $booking->isAvailable());
    }


    // Kiểm tra nhánh returnn false được thực thi hay không
    public function test_M11_PatientModel_update_01(){
        $booking = new PatientModel();
        $res = $booking->update();
        $this->assertFalse($res);
     }
     

     //Mục tiêu: kiểm tra nhánh isAvailable == true, và kiểm tra kết quả return là $this
     public function test_M11_PatientModel_update_02()
    {
        
        $booking = new PatientModel( 1);

        $booking->set("name", "Nguyen Tran Dat");

        $booking->update();

        $this->assertTrue($booking->isAvailable());
        $this->assertEquals("Nguyen Tran Dat", $booking->get("name"));
        $this->assertEquals(1 , $booking->get("id"));

        $getBooking = DB::table(TABLE_PREFIX.TABLE_PATIENTS)->where("id", "=", 1 )->get();
        $this->assertEquals(1, count($getBooking));
        $this->assertEquals(1 , $getBooking[0]->id);
        $this->assertEquals("Nguyen Tran Dat", $getBooking[0]->name);
    }


    // Kiểm tra nhánh returnn false được thực thi hay không
    public function test_M11_PatientModel_delete_01(){
        $booking = new PatientModel();
        $res = $booking->delete();
        $this->assertFalse($res);
     }
     

     //Mục tiêu: kiểm tra nhánh isAvailable == true, và kiểm tra kết quả return là $this
     public function test_M11_PatientModel_delete_02()
    {
        
        $booking = new PatientModel(1);

        $res = $booking->delete();
        $this->assertTrue($res);
        $this->assertFalse($booking->isAvailable());

        // $this->expectException(PDOException::class);
        $getBooking = DB::table(TABLE_PREFIX.TABLE_PATIENTS)->where("id", "=", 1)->get();
        $this->assertEquals(0, count($getBooking));
        
    }


}