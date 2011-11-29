<?php


/**
 * ある状態(HTMLなど)からある状態(HTML_Commentなど)へ遷移する際の条件を保持するクラス
 */
class Rule
{
  protected $_head;
  protected $_tail;
  protected $_escape;
  
  /**
   * 次の状態のID
   * @var string
   */
  protected $_next;
  
  /**
   * 終端文字列の他、改行によっても終了するかどうか
   * @var boolean
   */
  protected $_stopWithEol;
  
  public function __construct($next, $head, $tail, $escape, $stopWithEol)
  {
    $this->_next = $next;
    $this->_head = $head;
    $this->_tail = $tail;
    $this->_escape = $escape;
    $this->_stopWithEol = (boolean)$stopWithEol;
  }
  
  
  public function GetHead()
  { return $this->_head; }
  
  public function GetTail()
  { return $this->_tail; }
  
  public function GetEscape()
  { return $this->_escape; }
  
  public function GetNextState()
  { return $this->_next; }
  
  public function IsStopWithEol()
  { return $this->_stopWithEol;  }
  
  
  /**
   * ドキュメントの現在位置がルールの開始文字列とマッチするかを返します。
   * @param DocumentCharacterItor $documentItor ドキュメントの現在位置、内容を保持したオブジェクト
   * @return boolean 開始文字列とマッチしたかどうか
   */
  public function IsMatchWithHead(DocumentCharacterItor &$documentItor)
  {
    $headString = $this->_head;
    $criteria = $documentItor->SubString(0, strlen($headString));
    
    return (strcmp($headString, $criteria) == 0);
  }
}

?>