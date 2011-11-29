<?php

require_once dirname(__FILE__) . '/Partition.class.php';
require_once dirname(__FILE__) . '/Style.class.php';
require_once dirname(__FILE__) . '/TokenList.class.php';

/**
 * パーティション内のスタイルを走査するクラス
 *
 * パーティション上を1文字単位で走査し、該当する文字に適用するべきスタイルを
 * 返します(この時、パーティション内のトークンについても考慮します)。
 */
class PartitionStyleItor
{
  protected $_partition;
  protected $_cursor;

  protected $_currentToken;
  protected $_currentTokenIndex;
  
  protected $_offset;
  protected $_length;
  
  
  public function __construct(Partition $partition, $offset, $length)
  {
    $this->_partition = $partition;
    $this->_offset = $offset;
    $this->_length = $length;
    $this->_cursor = $offset;
    
    $this->_currentToken = NULL;
    $this->_currentTokenIndex = 0;
    $this->SetPartitionAndToken();
  }
  
  
  public function GetCurrent()
  {
    if($this->_partition === NULL) {
      return NULL;
    }
    if($this->IsEnd()) {
      return NULL;
    }
    
    $style = $this->_partition->GetState()->GetStyle();
    if($this->_currentToken !== NULL) {
      $tokenStyleId = $this->_currentToken->GetStyleId();
      $tokenStyle = $this->_partition->GetState()->GetLanguage()->GetStyleById($tokenStyleId);
      //$style->MergeStyle($tokenStyle);
      $style = $tokenStyle;
    }
    return $style;
  }
  
  
  public function Next($inc=1)
  {
    if($this->IsEnd()) {
      return;
    }
    $this->_cursor++;
    $this->SetPartitionAndToken();
  }
  
  
  protected function SetPartitionAndToken()
  {
    if($this->_partition === NULL) {
      return;
    }
    $partitionChanged = false;
    //パーティションの終端かどうかの判定。終端まで来ている場合は親パーティションに移動する
    if($this->_cursor >= $this->_partition->GetEndPosition()) {
      if($this->_partition->GetParent() !== NULL) {
        $this->_partition = $this->_partition->GetParent();
      } else {
        //パーティションの終端まで来ており、親が存在しない場合(=Rootノード)は走査終了
        return;
      }
    }
    
    //カーソル位置にあるパーティションを取得する(子ノード上に存在する場合は子ノードを探す)。
    $childNode = NULL;
    do {
      $childNode = $this->_partition->GetChildNodeAt($this->_cursor);
      if($childNode === $this->_partition) {
        //自分自身が返ってきた場合は見つからなかったと判断
        //これはGetChildNodeの設計ミス
        break;
      }
      if($childNode !== NULL) {
        $this->_partition = $childNode;
        $partitionChanged = true;
      }
    } while($childNode !== NULL);
    
    //パーティションが変わった場合はトークンを取得しなおして終了
    if($partitionChanged) {
      $this->_currentTokenIndex = $this->_partition->_tokenList->GetTokenIndexAt($this->_cursor);
      if($this->_currentTokenIndex == -1) {
        //トークンが存在しない
        $this->_currentToken = NULL;
      } else {
        //トークンが存在する
        $this->_currentToken = $this->_partition->_tokenList->GetTokenAtIndex($this->_currentTokenIndex);
      }
      return;
    }
    
    //パーティションが変わらない場合
    //現在のトークンを進める
    if(!$partitionChanged) {
      //トークンの終端まで来た場合、次のトークンを取得する
      if($this->_currentToken === NULL || $this->_cursor >= $this->_currentToken->GetEndPosition()) {
//      if($this->_cursor >= $this->_currentToken->GetEndPosition()) {
        $this->_currentTokenIndex = $this->_partition->_tokenList->GetTokenIndexAt($this->_cursor);
        if($this->_currentTokenIndex != -1) {
          $this->_currentToken = $this->_partition->_tokenList->GetTokenAtIndex($this->_currentTokenIndex);
        } else {
          $this->_currentToken = NULL;
        }
      }
    }
  }
  
  public function IsEnd()
  {
    if($this->_partition === NULL) {
      return true;
    }
    
    if($this->_partition->GetParent() === NULL && $this->_cursor >= $this->_partition->GetEndPosition()) {
      return true;
    }
    
    if($this->_cursor > $this->GetEndPosition()) {
      return true;
    }
    return false;
  }
  
  
  public function GetEndPosition()
  { return $this->_offset + $this->_length-1; }
  
  public function GetCurrentPartition()
  { return $this->_partition; }
  
  public function GetCurrentToken()
  { return $this->_currentToken; }
  
  public function GetPosition()
  { return $this->_cursor; }
}

?>