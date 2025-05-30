<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../api/app/core/DataList.php';
require_once __DIR__ . '/../../api/app/models/SpecialitiesModel.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';

class SpecialitiesModelTest extends TestCase
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

    public function test_M09_SpecialitiesModel_getAll_01()
    {
       
        // Lấy tổng số bản ghi trong DB
        $dbCount = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->count();

        // Gọi model để fetch data
        $specialities = new SpecialitiesModel();
        $specialities->fetchData();

        // So sánh số bản ghi model fetch được với DB
        $this->assertEquals($dbCount, $specialities->getTotalCount());
 
    }
    
}