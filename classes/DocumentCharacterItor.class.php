<?php

require_once dirname(__FILE__) . '/IDocumentCharacterItor.class.php';
require_once dirname(__FILE__) . '/Document.class.php';


/**
 * ドキュメントの特定の範囲を走査するクラス
 */
class DocumentCharacterItor implements IDocumentCharacterItor
{
  /**
   * 捜査対象のドキュメント。実際にはバッファになるかもしれない。
   * @var Document
   */
  protected $_document;
  
  /**
   * ドキュメント上での走査の開始位置
   * @var int
   */
  protected $_offset;
  
  /**
   * 走査の範囲
   * @var int
   */
  protected $_length;
  
  /**
   * 走査の現在位置
   * @var int
   */
  protected $_cursor;
  
  
  public function __construct(&$document, $offset, $length)
  {
    $this->Init($document, $offset, $length);
  }
  
  
  public function Init(Document &$document, $offset, $length)
  {
    $this->_document = &$document;
    $this->_offset = $offset;
    $this->_length = max(0, $length);
    $this->_cursor = $offset;
  }
  
  /**
   * 走査をn文字分進める
   * @param int $amount 進める量
   */
  public function Next($amount=1)
  {
    $amount = max(0,$amount);
    $endPosition = $this->_offset + $this->_length;
    
    if($this->_cursor + $amount > $endPosition) {
      $this->_cursor = $endPosition;
      return;
    }
    $this->_cursor += $amount;
  }
  
  /**
   * 走査をn文字分戻す
   * @param int $amount 戻る量
   */
  public function Prev($amount=1)
  {
    $amount = max(0,$amount);
    
    if($this->_cursor - $amount < $this->_offset) {
      $this->_cursor = $this->_offset;
      return;
    }
    $this->_cursor -= $amount;
  }
  
  /**
   * 現在位置の文字を返す
   * @return string
   */
  public function GetCurrent()
  { return $this->_document->GetAt($this->_cursor); }
  
  /**
   * 現在の位置を返す
   * @return int 
   */
  public function GetPosition()
  { return $this->_cursor; }
  
  /**
   * 始点かを返す
   * @return boolean
   */
  public function IsHead()
  { return ($this->_cursor == $this->_offset); }
  
  /**
   * 終点まで進んだかを返す
   * @return boolean
   */
  public function IsEnd()
  {
    if($this->_cursor >= $this->_document->GetContentLength()) {
      return true;
    }
    
    return ($this->_cursor == ($this->_offset + $this->_length));
  }
  
  /**
   * 指定された範囲の文字列を切り出した結果を返す
   * @return string 切り出した結果
   */
  public function SubString($begin, $length)
  {
    $begin = max(0, $begin);
    $length = max(0, $length);
    $result = '';
    $current = $this->_cursor + $begin;
    for($i=0; $i<$length; $i++)
    {
      $result .= $this->_document->GetAt($current++);
    }
    return $result;
  }

  public function SetPosition($position)
  { $this->_cursor = $position; }
}

?>