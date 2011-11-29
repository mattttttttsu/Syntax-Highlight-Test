<?php

require_once dirname(__FILE__) . '/Partition.class.php';
require_once dirname(__FILE__) . '/Token.class.php';
require_once dirname(__FILE__) . '/TokenList.class.php';


/**
 * パーティション内のトークンを順に返すクラス
 * 
 */
class PartitionTokenItor
{
  protected $_partition;
  protected $_cursor;
  protected $_position;
  protected $_length;
  
  public function __construct(Partition $partition, $position, $length)
  {
    $this->Init($partition, $position, $length);
  }
  
  
  public function GetCurrent()
  {
    return $this->_partition->_tokenList->GetTokenAtIndex($this->_cursor);
  }
  
  
  public function Next()
  {
    $token = $this->_partition->_tokenList->GetTokenAtIndex($this->_cursor++);
    
    if($token !== NULL) {
      return;
    }
    
    //トークンが取得できない -> リストの終端に来たと判断
    //親のパーティションを参照する
    $this->_cursor = -1;
    while($this->_cursor == -1 && $this->_partition->GetParent() !== NULL) {
      $this->_partition = $this->_partition->GetParent();
      $this->_cursor = $this->_partition->_tokenList->GetTokenIndexAt($position);
    }
  }
  
  
  /**
   * トークンの走査が終端まで進んだかを判定する
   **/
  public function IsEnd()
  {
    if($this->_partition === NULL) {
      return true;
    }
    
    //ルートパーティションでトークンが存在しない場合は終了
    if($this->_partition->GetParent() === NULL && $this->_cursor == -1) {
      return true;
    }
    
    $token = $this->GetCurrent();
    if($this->_partition->GetParent() === NULL && $token === NULL) {
      return true;
    }
    
    //トークンの始点が走査の終点を越えていたら終了
    if($token !== NULL && $token->GetOffset() >= $this->GetEndPosition()) {
      return true;
    }
    return false;
  }
  
  
  public function Init(Partition $partition, $position, $length)
  {
    $this->_partition = $partition->GetChildNodeAt($position);
    if($this->_partition === NULL) {
      return;
    }
    
    $this->_cursor = $this->_partition->_tokenList->GetTokenIndexAt($position);
    $this->_position = $position;
    $this->_length = $length;
  }
  
  
  public function GetEndPosition()
  { return $this->_position + $this->_length; }
  
}

?>