<?php

require_once 'PHPUnit/FrameWork.php';
require_once dirname(__FILE__) . '/common.php';
require_once dirname(__FILE__) . '/../classes/LanguageManager.class.php';


class LanguageManagerTest extends PHPUnit_Framework_TestCase
{
  public function testGetLanguageById()
  {
    $languageManager = &LanguageManager::GetInstance();
    
    $testXmlFile = dirname(__FILE__) . '/language_test.xml';
    $languageManager->LoadFromXML($testXmlFile);
    
    $language = &$languageManager->GetLanguageById('HTML');
    $this->assertTrue(is_a($language, 'Language'));
    
    $language = &$languageManager->GetLanguageById('AAA');
    $this->assertNull($language);
    
  }
}

?>