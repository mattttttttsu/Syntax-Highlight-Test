<?php

require_once 'PHPUnit/FrameWork.php';
require_once dirname(__FILE__) . '/common.php';
require_once dirname(__FILE__) . '/../classes/State.class.php';
require_once dirname(__FILE__) . '/../classes/LanguageManager.class.php';
require_once dirname(__FILE__) . '/../classes/Document.class.php';


class StateTest extends PHPUnit_Framework_TestCase
{
  protected $_state;
  protected $_stateHtmlString;
  
  public function setup()
  {
    $languageManager = &LanguageManager::GetInstance();
    $languageManager->LoadFromXML('language_test.xml');
    
    $language = $languageManager->GetLanguageById('HTML');
    $this->_state = $language->GetStateById('HTML');
    $this->_stateHtmlString = $language->GetStateById('HTML_STRING');
  }
  
  public function testFindRule()
  {
    $string = '<Test>';
    $document = new Document();
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $rule = &$this->_state->FindRule($documentItor);
    $this->assertTrue(is_a($rule, 'Rule'));
    $this->assertTrue($rule->GetHead() == '<');
    
    $string = '&lt;';
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $rule = &$this->_state->FindRule($documentItor);
    $this->assertNull($rule);
    
    $string = '<!-- aaa -->';
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $rule = &$this->_state->FindRule($documentItor);
    $this->assertType('Rule', $rule);
    $this->assertTrue($rule->GetHead() == '<!--');
    
    $string = '<![CDATA[ aaa ]]>';
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $rule = &$this->_state->FindRule($documentItor);
    $this->assertTrue(is_a($rule, 'Rule'));
    $this->assertTrue($rule->GetHead() == '<![CDATA[');
    
    $string = '<![cdata[ aaa ]]>';
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $rule = &$this->_state->FindRule($documentItor);
    $this->assertTrue(is_a($rule, 'Rule'));
    $this->assertTrue($rule->GetHead() == '<');
  }
  
  
  public function testIsStateEnd()
  {
    $string = '"aaaaa"';
    $document = new Document();
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(6, 100*1000);
    $rule = new Rule('HTML_STRING', '"', '"', '\"', false);
    
    //ルールをセットしないで呼び出した場合
    $this->assertFalse($this->_stateHtmlString->IsStateEnd($documentItor));
    
    $this->_stateHtmlString->SetCurrentRule($rule);
    $this->assertTrue($this->_stateHtmlString->IsStateEnd($documentItor));
    
    //終端の一文字手前から
    $documentItor = &$document->GetDocumentItor(5, 100*1000);
    $this->assertFalse($this->_stateHtmlString->IsStateEnd($documentItor), $documentItor->GetCurrent());
    
  }

  public function testIsEscape()
  {
    $string = '"aaa\"aaa"';
    $document = new Document();
    $document->SetContent($string);
    $documentItor = &$document->GetDocumentItor(4, 100*1000);
    
    //ルールをセットしていなければ反応しない
    $rule = new Rule('HTML_STRING', '"', '"', '\"', false);
    $this->assertFalse($this->_stateHtmlString->IsEscape($documentItor));
    
    $this->_stateHtmlString->SetCurrentRule($rule);
    $this->assertEquals('\"', $this->_stateHtmlString->GetCurrentRule()->GetEscape());
    $this->assertTrue($this->_stateHtmlString->IsEscape($documentItor));
    
    //エスケープ出ない所では反応しない
    $documentItor = &$document->GetDocumentItor(0, 100*1000);
    $this->assertFalse($this->_stateHtmlString->IsEscape($documentItor));
    
  }
}

?>