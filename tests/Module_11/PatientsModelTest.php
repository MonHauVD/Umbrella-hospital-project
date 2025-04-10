<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../api/app/core/DataList.php';
require_once __DIR__ . '/../../api/app/models/PatientsModel.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';

class PatientsModelTest extends TestCase
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
    public function test_M08_AppoinmentModel_getAll_01()
    {
       
        $booking = new PatientsModel();
        $booking->fetchData();
        $count = count($booking->getData());
        $this->assertEquals(17, $count);
        

        // Verify in DB
        $dbBooking = DB::table(TABLE_PREFIX.TABLE_PATIENTS)->get();
        $this->assertEquals(count($dbBooking), $count);
    }
}