<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;


class BookingModelsTest extends TestCase
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
    public function test_M07_BookingModels_getAll_01()
    {
       
        $booking = new BookingsModel();
        $booking->fetchData();
        
        $this->assertEquals(27, count($booking->getData()));

        // Verify in DB
        // $dbBooking = DB::table(TABLE_PREFIX.TABLE_BOOKINGS)->where("id", "=", $ids)->get();
        // $this->assertEquals(1, count($dbBooking));
        // $this->assertEquals( $dbBooking[0]->id, $booking->get("id"));
        // $this->assertEquals( $dbBooking[0]->booking_name, $booking->get("booking_name"));
        // $this->assertEquals($dbBooking[0]->booking_phone, $booking->get("booking_phone"));
    }


}