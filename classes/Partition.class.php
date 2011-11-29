<?php

require_once dirname(__FILE__) . '/DocumentCharacterItor.class.php';
require_once dirname(__FILE__) . '/TokenList.class.php';
require_once dirname(__FILE__) . '/State.class.php';

/**
 * ドキュメントをコメント/文字列/通常部分等の領域に分割するクラス
 * DOMのようなツリー構造で管理します。
 */
class Partition
{
  /**
   * 子ノード(パーティション)のリスト
   * @var Array(Partition)
   */
  protected $_children;
  
  /**
   * パーティション内に含まれるトークン(キーワードやその他の文字列)のリスト
   * @var Array(Token)
   */
  public $_tokenList;
  
  protected $_offset;
  protected $_length;
  
  /**
   * 現在のパーティションの状態(HTML/HTML_COMMENT/PHP等)。
   * @var State
   */
  protected $_state;
  
  
  /**
   * 親ノード
   * @var Partition
   */
  protected $_parent;
  
  protected $_prev = NULL;
  protected $_next = NULL;
  
  public function __construct(&$parent, State $state, $offset, $length)
  {
    $this->_children = array();
    $this->_tokenList = new TokenList();
    
    $this->_offset = (int)$offset;
    $this->_length = (int)$length;
    
    $this->_parent = &$parent;
    
    $this->_state = $state;
  }
  
  
  public function AppendChild(&$newPartition)
  {
    $newPartition->SetParent($this);
    $this->_children[] = $newPartition;
  }
  
  
  public function RemoveNode(&$child)
  {
    foreach($this->_children as $idx=>$unused)
    {
      if($this->_children[$idx] === $child) {
        unset($this->_children[$idx]);
        break;
      }
    }
  }
  
  
  public function &GetChildNodeAt($position)
  {
    $node = NULL;
    $node = &$this->findPartitionAt($position);
    return $node;
  }
  
  
  /**
   * 指定された点以降の全パーティションを削除します。
   * 
   * @param int $offset 削除の基準となる点
   */
  public function &RemovePartitionsAfterPosition($offset, $targetPartition=NULL)
  {
    foreach($this->_children as $idx=>$child)
    {
      //削除対象の点を含んだパーティションを見つけた場合、そこから後のパーティションはすべて削除対象とします。
      if($targetPartition === NULL && $child->IsOn($offset)) {
        $targetPartition = clone $this->_children[$idx];
        unset($this->_children[$idx]);
        continue;
      }
      
      if($child->GetChildrenCount() > 0) {
        $targetPartition = &$this->_children[$idx]->RemovePartitionsAfterPosition($offset, $targetPartition);
      }
      
      if($targetPartition !== NULL && $child->IsUnderBypartition($targetPartition)) {
        unset($this->_children[$idx]);
      }
    }
    
    $this->_tokenList->RemoveTokensAfterPosition($position);
    
    //ルートノードの場合は削除出来ないため初期化を行う。
    if($targetPartition === NULL) {
      if($this->_parent === NULL && $this->IsOn($offset)) {
        $this->_children = array();
        $this->_tokenList = new TokenList();
        
        $this->_offset = 0;
        $this->_length = 0;
      }
    }
    
    return $targetPartition;
  }
  
  
  public function GetState()
  { return $this->_state; }
  
  
  public function GetOffset()
  { return $this->_offset; }
  
  
  public function GetLength()
  { return $this->_length; }
  
  
  public function GetEndPosition()
  { return max(0, $this->_offset+$this->_length-1); }
  
  
  public function &GetParent()
  { return $this->_parent; }
  
  
  public function GetChildrenCount()
  { return count($this->_children); }
  
  
  public function SetLength($newLength)
  { $this->_length = (int)$newLength; }
  
  
  public function SetParent(&$newParent)
  {
    //現在既に親が存在する場合はその親から削除する
    if($this->_parent != NULL) {
      $this->_parent->RemoveNode($this);
      $this->_parent = NULL;
    }
    
    $this->_parent = &$newParent;
  }
  
  public function &findPartitionAt($position)
  {
    $null = NULL;
    $position = (int)$position;
    
    if($position < $this->_offset || $position > $this->GetEndPosition()) {
      return $null;
    }
    
    $count = count($this->_children);
    foreach($this->_children as $idx=>$child)
    {
      $found = &$this->_children[$idx]->findPartitionAt($position);
      if($found != NULL) {
        return $found;
      }
    }
    
    return $this;
  }
  
  
  public function Update(IDocumentCharacterItor &$documentItor)
  {
    $startPartition = $this->GetChildNodeAt($documentItor->GetPosition());
    
    //更新を開始する前に、解析開始位置以降にあるパーティションをすべて削除する
    $rootNode = &$this->GetRootNode();
    $rootNode->RemovePartitionsAfterPosition($documentItor->GetPosition());
    
    //改めてスタート位置のパーティションを取得する
    $startPartition = &$this->GetChildNodeAt($documentItor->GetPosition());
    $startPosition = $startPartition->GetOffset();
    $startPartition->SetLength(0);
    $documentItor->SetPosition($startPosition);
    $startPartition->ClearTokenList();
    
    $startPartition->UpdatePartitionR($documentItor);
    
    //現在のノードからルートノードまで遡って更新する
    //ドキュメント全体の解析が終わっているかどうかにかかわらず行う
    $parentNode = &$startPartition->_parent;
    while($parentNode != NULL)
    {
      //ドキュメントの解析開始位置と親パーティションの終端が異なる可能性があるため、
      //調整を行う。
      $diff = $documentItor->GetPosition() - $parentNode->GetEndPosition();
      $parentNode->SetLength($parentNode->GetLength() + $diff);
      
      $parentNode->UpdatePartitionR($documentItor);
      $parentNode = &$parentNode->GetParent();
    }
  }
  
  
  public function UpdatePartitionR(DocumentCharacterItor &$documentItor)
  {
    while(!$documentItor->IsEnd()) {
      
      $stateCurrentRule = $this->_state->GetCurrentRule();
      if($this->_length > 0 && $this->_state->IsStateEnd($documentItor)) {
        //閉じタグや閉じのダブルクォート等が見つかった場合は、スキップして
        //状態を終了する。
        $tailLen = strlen($this->_state->GetCurrentRule()->GetTail());
        for($i=0;$i<$tailLen;$i++) {
          $documentItor->Next();
          $this->_length++;
        }
        return;
      }
      
      if($this->_length > 0 && $stateCurrentRule !== NULL && $stateCurrentRule->IsStopWithEol()) {
        //改行で終了するルールの場合、現在位置が改行かどうかを判定する
        $isLF = $documentItor->GetCurrent() == "\n";
        $isCRLF = $documentItor->SubString(0, 2) == "\r\n";
        $isCR = (!$isCRLF && $documentItor->GetCurrent() == "\r");
        $lineFeedLength = ($isCRLF) ?  2 : 1;
        
        if($isLF || $isCR || $isCRLF) {
          $documentItor->Next($lineFeedLength);
          $this->_length += $lineFeedLength;
          return;
        }
      }
      
      if($this->_length > 0 && $this->_state->IsEscape($documentItor)) {
        //エスケープ文字列が見つかった場合はエスケープ文字列を抜けるまでスキップする
        $escapeLen = strlen($this->_state->GetCurrentRule()->GetEscape());
        for($i=0;$i<$escapeLen;$i++) {
          $documentItor->Next();
          $this->_length++;
        }
        continue;
      }
      
      //キーワードに関する処理

      if($this->eatBlank($documentItor) > 0) {
        continue;
      }
      if($this->eatWords($documentItor) > 0) {
        continue;
      }

      $rule = $this->_state->FindRule($documentItor);
      if($rule === NULL) {
        $documentItor->Next();
        $this->_length++;
        continue;
      }
      
      //ルールが見つかった場合、次の状態に遷移する
      $newStateId = $rule->GetNextState();
      
      $language = $this->_state->GetLanguage();
      $newState = $language->GetStateById($newStateId);
      $newState->SetCurrentRule($rule);
      $newPartition = new Partition($this, $newState, $documentItor->GetPosition(), 0);
      
      $this->AppendChild($newPartition);
      $newPartition->UpdatePartitionR($documentItor);
      
      $this->_length += $newPartition->GetLength();
    }
    
  }
  
  
  /**
   * ドキュメントの現在位置から、スペース/タブ/空白を読み飛ばします。
   * 現在位置がスペース/タブ/空白以外の場合は何もせずに処理を抜けます。
   */
  protected function eatBlank(DocumentCharacterItor $documentItor)
  {
    $isBlank = true;
    while(!$documentItor->IsEnd())
    {
      $char = $documentItor->GetCurrent();
      switch($char)
      {
        case "\r":
        case "\n":
        case "\t":
        case " ":
          $this->_length++;
          $documentItor->Next();
          $len++;
          break;
        default:
          return $len;
      }
    }
    return $len;
  }
  
  
  /**
   * ドキュメントの現在位置から、スペース/タブ/改行以外の文字列の解析を行います。
   * スペース/タブ/改行、および英数字以外が見つかった場合は処理を抜けます。
   * 解析中にキーワードを発見した場合はトークンとして登録を行います。
   */
  protected function eatWords(DocumentCharacterItor $documentItor)
  {
    $isBlank = true;
    $start = $documentItor->GetPosition();
    $word = '';
    $len = 0;
    while(!$documentItor->IsEnd())
    {
      $char = $documentItor->GetCurrent();
      if(!preg_match('/[A-Z0-9_]+/i', $char)) {
        break;
      }
      
      $word .= $char;
      $this->_length++;
      $documentItor->Next();
      $len++;
    }
    $keyword = $this->_state->FindKeywordByString($word);
    if($keyword === NULL) {
      return $len;
    }
    //抜き出した単語がキーワードとマッチした場合はトークンリストに追加する。
    $token = new Token($start, strlen($keyword->GetWord()), $keyword->GetStyleId(), $keyword->GetWord());
    
    $this->_tokenList->AddToken($token);
    return $len;
  }
  
  
  /**
   * 指定された点がパーティションに含まれているかどうかを返します。
   * @return boolean 点が含まれているかどうか
   */
  public function IsInclude($position)
  {
    return ($position >= $this->_offset && $position <= $this->GetEndPosition());
  }
  
  
  /**
   * 指定された点が(子ノードではなく)自分自身の領域内にあるかを返します。
   * 指定された点が子ノード内に含まれている場合はFalseを返します。
   * @return boolean 点が含まれているかどうか
   */
  public function IsOn($position)
  {
    foreach($this->_children as $idx=>$child)
    {
      if($child->IsOn($position)) {
        return false;
      }
    }
    
    return $this->IsInclude($position);
  }
  
  
  /**
   * 指定されたパーティションと範囲の比較を行い、比較対象よりも
   * 自分が下に存在するかを返す
   */
  public function IsUnderByPartition(&$partition)
  {
    return $this->_offset >= $partition->GetEndPosition();
  }
  
  
  /**
   * ルートノードを返す
   * @return Partition ルートノード
   */
  public function &GetRootNode()
  {
    $parent = $this;
    while($parent !== NULL) {
      if($parent->GetParent() === NULL) {
        break;
      }
      $parent = $parent->GetParent();
    }
    return $parent;
  }
  
  
  public function ClearTokenList()
  {
    $this->_tokenList->ClearList();
  }
  
  /**
   * 現在のパーティションの構造を文字列で出力します(デバッグ用)
   */
  public function dumpPartitionStructure($indent='')
  {
    $buffer = '';
    
    $state = $this->_state->GetId();
    $head = ($this->_state->GetCurrentRule() !== NULL) ? $this->_state->GetCurrentRule()->GetHead() : '';
    $tail = ($this->_state->GetCurrentRule() !== NULL) ? $this->_state->GetCurrentRule()->GetTail() : '';
    
    $buffer = sprintf('%sSTATE:%s POS:%d-%d(%d) HEAD:%s TAIL:%s'."\n",
                      $indent, $state, $this->_offset, $this->_length, $this->GetEndPosition(),
                      $head, $tail);
    
    foreach($this->_children as $idx=>$child)
    {
      $buffer .= $child->dumpPartitionStructure($indent."\t");
    }
    
    return $buffer;
  }
  
  
}


?>