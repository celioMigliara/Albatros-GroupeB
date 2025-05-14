<?php

define('PHPUNIT_RUNNING', true);

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

require_once __DIR__ . '/../../../Model/B3/db_connect.php';
require_once __DIR__ . '/../../../Model/B3/Role.php';
require_once __DIR__ . '/../../../Controller/B3/PrintController.php';
require_once __DIR__ . '/../../../Test/B3/BaseTestClass.php';
require_once __DIR__ . '/../../../Model/B3/MessageErreur.php';

class PrintControllerTest extends BaseTestClass
{
    private $printController;

    /* ========================================================== */
    /* ========== TESTS IMPRESSION LISTE TÂCHES ================ */
    /* ========================================================== */
    public function testImpressionListeTachesNonConnecte()
    {
        $this->printController = new PrintController();
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $result = $this->printController->print();
        $output = ob_get_clean();

        $this->assertFalse($result);
        $this->assertStringContainsString("Veuillez vous identifier en tant qu'administrateur", $output);
    }


} 