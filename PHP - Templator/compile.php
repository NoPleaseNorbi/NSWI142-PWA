#!/usr/bin/php
<?php

require_once __DIR__ . '/templator.php';

$templator = new Templator();
$templator->loadTemplate('./template.tpl.html');
$templator->compileAndSave('./template.php');
