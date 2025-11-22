<?php
require_once __DIR__ . '/model/Item.php';
require_once __DIR__ . '/model/ItemRepository.php';
require_once __DIR__ . '/view/ItemView.php';
require_once __DIR__ . '/view/MenuView.php';
require_once __DIR__ . '/controller/ItemController.php';

$repo = new ItemRepository();
$view = new ItemView();
$menu = new MenuView();
$controller = new ItemController($repo, $view, $menu);

$controller->run();
