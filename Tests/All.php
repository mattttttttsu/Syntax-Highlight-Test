<?php

require_once dirname(__FILE__) . '/common.php';
require_once 'PHPUnit/Framework.php';

class AllTest
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    require_once 'LanguageManagerTest.php';
    $suite->addTestSuite('LanguageManagerTest');
    
    require_once 'LanguageTest.php';
    $suite->addTestSuite('LanguageTest');
    
    require_once 'RuleTest.php';
    $suite->addTestSuite('RuleTest');
    
    require_once 'StateTest.php';
    $suite->addTestSuite('StateTest');
    
    require_once 'DocumentCharacterItorTest.php';
    $suite->addTestSuite('DocumentCharacterItorTest');
    
    require_once 'TokenListTest.php';
    $suite->addTestSuite('TokenListTest');
    
    require_once 'PartitionTest.php';
    $suite->addTestSuite('PartitionTest');
    
    require_once 'PartitionStyleItorTest.php';
    $suite->addTestSuite('PartitionStyleItorTest');
    
    return $suite;
  }
}

?>