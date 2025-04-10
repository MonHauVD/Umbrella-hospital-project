<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../api/app/core/DataList.php';
require_once __DIR__ . '/../../api/app/models/BookingPhotosModel.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';

class BookingPhotosModelTest extends TestCase
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

    public function test_M01_BookingPhotosModel_getAll_01()
    {
       
        $bookingPhotos = new BookingPhotosModel();
        $bookingPhotos->fetchData();
        $this->assertEquals(16, $bookingPhotos->getTotalCount());
    }
}