<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../api/app/core/DataList.php';
require_once __DIR__ . '/../../api/app/models/AppointmentsModel.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';

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
       
        $booking = new AppointmentsModel();
        $booking->fetchData();
        
        $this->assertEquals(17, count($booking->getData()));

        // Verify in DB
        $dbBooking = DB::table(TABLE_PREFIX.TABLE_APPOINTMENTS)->get();
        $this->assertEquals(count($dbBooking), count($booking->getData()));
    }
}