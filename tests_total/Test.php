<?php

if (!defined('APPPATH')) {
    define('APPPATH', realpath(__DIR__ . '/../api/app'));
}
if (!defined('EC_SALT')) {
    define('EC_SALT', 'your_test_secret_key_here');
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', __DIR__ . '/../tests_total/Module_03');
}
if (!function_exists('__')) {
    function __($str) {
        return $str;
    }
}


require_once __DIR__ . '/../api/app/core/DataList.php';
require_once __DIR__ . '/../api/app/models/BookingPhotosModel.php';
require_once __DIR__ . '/../api/app/config/db.config.php';
require_once __DIR__ . '/../api/app/core/DataEntry.php';
require_once __DIR__ . '/../api/app/models/DrugModel.php';
require_once __DIR__ . '/../api/app/models/DrugsModel.php';
require_once __DIR__ . '/../api/app/models/TreatmentsModel.php';
require_once __DIR__ . '/../api/app/core/Controller.php';
require_once __DIR__ . '/../api/app/core/Input.php';
require_once __DIR__ . '/../api/app/helpers/common.helper.php';
require_once __DIR__ . '/../tests_total/Module_03/helper/TestableHelperController.php';
require_once __DIR__ . '/../tests_total/Module_03/helper/FakeAuthUser.php';
require_once __DIR__ . '/../api/app/controllers/PatientProfileController.php';
require_once __DIR__ . '/../api/app/controllers/LoginController.php';
require_once __DIR__ . '/../api/app/controllers/LoginWithGoogleController.php';
require_once __DIR__ . '/../tests_total/Module_04/helper/TestableHelperController4.php';

require_once __DIR__ . '/../api/app/core/DataEntry.php';
require_once __DIR__ . '/../api/app/core/Controller.php';
require_once __DIR__ . '/../api/app/models/AppointmentRecordModel.php';
require_once __DIR__ . '/../umbrella-corporation/app/models/UserModel.php';
require_once __DIR__ . '/../api/app/config/db.config.php';
require_once __DIR__ . '/../ConfigDefine.php';
require_once __DIR__ . '/../api/app/models/BookingPhotoModel.php';
require_once __DIR__ . '/../api/app/models/TreatmentModel.php';
require_once __DIR__ . '/../api/app/models/BookingsModel.php';
require_once __DIR__ . '/../api/app/models/BookingModel.php';
require_once __DIR__ . '/../api/app/models/AppointmentModel.php';
require_once __DIR__ . '/../api/app/models/AppointmentsModel.php';
require_once __DIR__ . '/../api/app/models/SpecialitiesModel.php';
require_once __DIR__ . '/../api/app/models/SpecialityModel.php';
require_once __DIR__ . '/../api/app/core/Input.php';
require_once __DIR__ . '/../api/app/controllers/DoctorsController.php';
require_once __DIR__ . '/../api/app/models/DoctorModel.php';
require_once __DIR__ . '/../api/app/core/Input.php';
require_once __DIR__ . '/../api/app/controllers/DoctorController.php';
require_once __DIR__ . '/../api/app/models/PatientModel.php';
require_once __DIR__ . '/../api/app/models/PatientsModel.php';
require_once __DIR__ . '/../tests_total/Module_12/helper/TestableHelperController12.php';

require_once __DIR__ . '/../tests_total/Module_12/helper/FakeInput12.php';

