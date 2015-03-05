<?php
include ("Sls/Generics/SLS_Generic.class.php");
$genericLib = SLS_Generic::getInstance()->loadFramework();
$frontController = SLS_FrontController::getInstance($genericLib);
$frontController->loadController();
new SLS_View($genericLib,$frontController->getXML());
?>