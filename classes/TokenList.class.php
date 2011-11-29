<?php

require_once dirname(__FILE__) . '/Token.class.php';

class TokenList
{
  /**
   * トークンの配列
   * @var Array(Token)
   */
  protected $_tokens;
  
  public function __construct()
  {
    $this->_tokens = array();
  }
  
  
  public function AddToken($token)
  {
    $this->_tokens[] = $token;
  }
  
  
  public function GetTokenCount()
  { return count($this->_tokens); }
  
  public function GetTokenAtIndex($index)
  {
    if(!isset($this->_tokens[$index])) {
      return NULL;
    }
    return $this->_tokens[$index];
  }
  
  /**
   * 指定された点を含むトークンのインデックス番号を返します。
   * 見つからない場合は-1を返します。
   */
  public function GetTokenIndexAt($position)
  {
    foreach($this->_tokens as $idx=>$token)
    {
      if($token->IsInclude($position)) {
        return $idx;
      }
    }
    return -1;
  }
  
  public function ClearList()
  {
    $this->_tokens = array();
  }
  
  
  /**
   * 指定された点を含む、または点よりも後に存在するトークンを削除します。
   */
  public function RemoveTokensAfterPosition($position)
  {
    foreach($this->_tokens as $idx=>$token)
    {
      if($token->GetEndPosition() >= $position) {
        unset($this->_tokens[$idx]);
      }
    }
  }
  
  /**
   * トークンの内容を一括出力する。(DEBUG用)
   */
  public function DumpTokenStructure()
  {
    $buffer = 'Content========================================================='."\n";
    foreach($this->_tokens as $token)
    {
      $buffer .= sprintf('POS:%6d+%6d S:%10s %s'."\n", $token->GetOffset(),
                         $token->GetLength(), $token->GetStyleId(), $token->GetContent());
    }
    return $buffer;
  }
  
}

?>