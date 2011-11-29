<?php

require_once 'PHPUnit/FrameWork.php';
require_once dirname(__FILE__) . '/common.php';
require_once dirname(__FILE__) . '/../classes/Rule.class.php';
require_once dirname(__FILE__) . '/../classes/LanguageManager.class.php';


class RuleTest extends PHPUnit_Framework_TestCase
{
  protected $_rule;
  protected $_ruleXmlComment;
  
  public function setup()
  {
    $languageManager = &LanguageManager::GetInstance();
    $languageManager->LoadFromXML('language_test.xml');
    
    $language = $languageManager->GetLanguageById('HTML');
    $rules = $language->GetRuleListByStateId('HTML');
    $this->_rule = $rules[4];
    $this->_ruleXmlComment = $rules[2];
  }
  
  public function testIsMatchWithHead()
  {
    $string = '<Test>';
    $document = new Document();
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $this->assertTrue($this->_rule->IsMatchWithHead($documentItor));
    
    $string = '&lt;';
    $document = new Document();
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $this->assertFalse($this->_rule->IsMatchWithHead($documentItor));
    
    $string = '<![CDATA[ aaaaa ]]>';
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $this->assertTrue($this->_ruleXmlComment->IsMatchWithHead($documentItor));
    
    $string = '<![cdata[ aaaaa ]]>';
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $this->assertFalse($this->_ruleXmlComment->IsMatchWithHead($documentItor));
    
    $string = '<Test>';
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $this->assertFalse($this->_ruleXmlComment->IsMatchWithHead($documentItor));
  }
}

?>