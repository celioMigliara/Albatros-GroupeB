<?php

namespace App\core;

abstract class Controller
{
    protected function render(string $view, array $params = [])
    {
        extract($params);
        ob_start();
        require "View/{$view}.php";
        $content = ob_get_clean();
        require "View/B5/AccueilAdmin.php";
    }
}