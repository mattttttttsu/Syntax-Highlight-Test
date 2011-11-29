<?php

require_once 'PHPUnit/FrameWork.php';
require_once dirname(__FILE__) . '/common.php';
require_once dirname(__FILE__) . '/../classes/Partition.class.php';
require_once dirname(__FILE__) . '/../classes/TokenList.class.php';


class TokenListTest extends PHPUnit_Framework_TestCase
{
  protected $_tokenList;
  
  public function setup()
  {
    $this->_tokenList = new TokenList();
  }
  
  public function testGetTokenIndexAt()
  {
    $this->_tokenList->AddToken(new Token(0, 5, 'test', 'aaaaa'));
    $this->_tokenList->AddToken(new Token(5, 5, 'test', 'bbbbb'));
    $this->_tokenList->AddToken(new Token(10, 5, 'test', 'ccccc'));
    
    $this->assertEquals(3, $this->_tokenList->GetTokenCount());
    
    //適当な場所を指定して正しく動作する
    $criteria = $this->_tokenList->GetTokenIndexAt(4);
    $this->assertEquals(0, $criteria);
    $criteria = $this->_tokenList->GetTokenIndexAt(5);
    $this->assertEquals(1, $criteria);
    $criteria = $this->_tokenList->GetTokenIndexAt(9);
    $this->assertEquals(1, $criteria);
    $criteria = $this->_tokenList->GetTokenIndexAt(10);
    $this->assertEquals(2, $criteria);
    $criteria = $this->_tokenList->GetTokenIndexAt(20);
    $this->assertEquals(-1, $criteria);
  }
  
  public function testRemoveTokensAfterPosition()
  {
    $this->_tokenList->AddToken(new Token(0, 5, 'test', 'aaaaa'));
    $this->_tokenList->AddToken(new Token(5, 5, 'test', 'bbbbb'));
    $this->_tokenList->AddToken(new Token(10, 5, 'test', 'ccccc'));
    
    $this->assertEquals(3, $this->_tokenList->GetTokenCount());
    
    //適当な場所を削除して、残るべきデータが残る事を確認
    $this->_tokenList->RemoveTokensAfterPosition(5);
    $criteria = $this->_tokenList->GetTokenCount();
    $this->assertEquals(1, $criteria);
    $criteria = $this->_tokenList->GetTokenIndexAt(5);
    $this->assertEquals(-1, $criteria);
    
    //見当違いな場所を削除しても問題ない事を確認
    $this->_tokenList->ClearList();
    $this->_tokenList->AddToken(new Token(0, 5, 'test', 'aaaaa'));
    $this->_tokenList->AddToken(new Token(5, 5, 'test', 'bbbbb'));
    $this->_tokenList->AddToken(new Token(10, 5, 'test', 'ccccc'));
    $this->_tokenList->RemoveTokensAfterPosition(105);
    
    $this->assertEquals(3, $this->_tokenList->GetTokenCount());
  }
  
}

?>