<?php

require_once 'PHPUnit/FrameWork.php';
require_once dirname(__FILE__) . '/common.php';
require_once dirname(__FILE__) . '/../classes/Language.class.php';
require_once dirname(__FILE__) . '/../classes/LanguageManager.class.php';


class LanguageTest extends PHPUnit_Framework_TestCase
{
  protected $_language = NULL;
  
  public function setUp()
  {
    $languageManager = &LanguageManager::GetInstance();
    $languageManager->LoadFromXML('language_test.xml');
    
    $this->_language = $languageManager->GetLanguageById('HTML');
  }
  
  public function testInitByArray()
  {
    $this->assertTrue(is_a($this->_language, 'Language'));
  }
  
  
  public function testGetStateById()
  {
    $state = $this->_language->GetStateById('HTML');
    $this->assertTrue(is_a($state, 'State'));
    
    $state = $this->_language->GetStateById('HTML2');
    $this->assertTrue($state === NULL);
  }
  
  
  public function testGetRuleListByStateId()
  {
    $rules = $this->_language->GetRuleListByStateId('HTML');
    $this->assertEquals(5, count($rules));
    $this->assertType('Rule', $rules[0]);
  }


  public function testGetKeywordListByStateId()
  {
  }
}

?>