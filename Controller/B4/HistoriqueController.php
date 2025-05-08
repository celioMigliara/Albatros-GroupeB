<?php
require_once __DIR__ . '/../../Model/B4/Historique.php';
use Model\B4\Historique;

class HistoriqueController
{
    public function index()
    {
        $model      = new Historique();
        $historique = $model->getAll();
        require __DIR__ . '/../../View/B4/Historique/index.php';
    }
}
