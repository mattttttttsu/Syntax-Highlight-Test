<?php

/**
 * 各言語の状態に応じたキーワード(if,forなど)の情報を保持するクラス
 */
class Keyword
{
  
  protected $_styleId;
  
  protected $_word;
  
  public function __construct($word, $styleId)
  {
    $this->_word = $word;
    $this->_styleId = $styleId;
  }
  
  
  public function GetWord()
  { return $this->_word; }
  
  public function GetStyleId()
  { return $this->_styleId; }
}


?>