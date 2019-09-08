<?php

$config = include 'config/config.php';
include 'bootstrap/Psr4Autoload.php';
include "bootstrap/Start.php";
include 'bootstrap/Alias.php';

Start::router();
