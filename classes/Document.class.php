<?php

require_once dirname(__FILE__) . '/DocumentCharacterItor.class.php';

/**
 * ドキュメントのクラス
 */
class Document
{
  protected $_content;
  
  public function __construct()
  {
    $this->_content = '';
  }
  
  
  public function SetContent(&$content)
  {
    $this->_content = &$content;
  }
  
  
  /**
   * ドキュメント中の、指定された位置にある文字を返す
   */
  public function GetAt($position)
  {
    return substr($this->_content, $position, 1);
  }
  
  
  /**
   * ドキュメント本文の長さを返す
   */
  public function GetContentLength()
  { return strlen($this->_content); }
  
  /**
   * ドキュメントの指定された範囲を走査するオブジェクトを返す。
   * @return DocumentCharacterItor
   */
  public function &GetDocumentItor($offset, $length)
  {
    $itor = new DocumentCharacterItor($this, $offset, $length);
    return $itor;
  }
}

?>