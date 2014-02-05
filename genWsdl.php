<?php
include("soap/WSDLCreator.php");
$wsdlgen = new WSDLCreator("UrlMatcherWSDL", "http://localhost/test/wsdl");
$wsdlgen->addFile("UrlMatcher.php");
$wsdlgen->addURLToClass("UrlMatcher","http://localhost/test/UrlMatcher.php");
$wsdlgen->createWSDL();
$wsdlgen->saveWSDL("wsdl");
?>