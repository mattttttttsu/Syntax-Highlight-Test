<?php

require_once dirname(__FILE__) . '/IDocumentCharacterItor.class.php';
require_once dirname(__FILE__) . '/Rule.class.php';
require_once dirname(__FILE__) . '/Style.class.php';

/**
 * 現在の解析の状態(通常/HTMLコメント内/HTMLタグ内/HTML文字列内/PHP文字列内など)を表すクラス。
 * 現在の状態からどのような状態に遷移し得るかの情報(Rule)も含んでいます。
 */
class State
{
  /**
   * 現在の状態から遷移し得る状態の一覧。
   * 開始文字列や終端文字列等も含んでいる。
   * @var Array(Rule)
   */
  protected $_ruleList;
  
  /**
   * 現在の状態で有効なキーワードの一覧。
   * @var Array(Keyword)
   */
  protected $_keywordList;
  
  /**
   * 現在の状態の言語
   * @var Language
   */
  protected $_language;
  
  /**
   * 状態の名前(HTML,HTML_Commentなど)
   * @var string
   */
  protected $_id;
  
  /**
   * この状態が使用するスタイルID(テキストの装飾)
   * @var string
   */
  protected $_styleId;
  
  /**
   * 現在適用されているルール。
   * 開始文字列/終端文字列、エスケープ記号等
   * @var Rule
   */
  protected $_currentRule;
  
  public function __construct(&$language, $id, $styleId)
  {
    $this->_language = &$language;
    $this->_id = $id;
    $this->_styleId = $styleId;
    $this->_ruleList = &$this->_language->GetRuleListByStateId($this->_id);
    $this->_keywordList = &$this->_language->GetKeywordListByStateId($this->_id);
    
    if($this->_ruleList === NULL) {
    	$this->_ruleList = array();
    }
    if($this->_keywordList === NULL) {
    	$this->_keywordList = array();
    }
  }
  
  
  function SetCurrentRule(Rule &$rule)
  { return $this->_currentRule = $rule; }
  
  
  /**
   * ドキュメントの現在位置にマッチするルールを返します
   * @return Rule マッチしたルール。見つからなかった場合はNULL。
   */
  public function &FindRule(DocumentCharacterItor &$documentItor)
  {
    //ドキュメントの現在位置とマッチするルールを調べます。
    //リスト内で最初に見つかった要素が候補となるため、
    //"/**"(DocComment)と"/*"(コメント)のように、"途中まで同じ"関係の場合は
    //"長さの長い方"(上記の場合DocComment)をリストの上側に配置しておく必要があります。
    $result = NULL;
    foreach($this->_ruleList as $idx=>$rule)
    {
      if($rule->IsMatchWithHead($documentItor)) {
        $result = &$this->_ruleList[$idx];
        break;
      }
    }
    return $result;
  }
  
  /**
   * 指定された文字列のキーワードが存在する場合、そのキーワードを返します。
   * @return Keyword マッチしたキーワード。マッチしなかった場合はNULL
   */
  public function FindKeywordByString($string)
  {
    //TODO:Case-Sensitiveの問題への対処
    if(isset($this->_keywordList[$string])) {
      return $this->_keywordList[$string];
    }
    
    return NULL;
  }
  
  
  public function GetId()
  { return $this->_id; }
  
  
  public function &GetLanguage()
  { return $this->_language; }
  
  
  public function &GetCurrentRule()
  { return $this->_currentRule; }
  
  public function GetStyleId()
  { return $this->_styleId; }
  
  public function GetStyle()
  {
    return $this->_language->GetStyleById($this->_styleId);
  }
  
  
  /**
   * 現在のドキュメント位置が状態の終点にあるかを返す
   * @return boolean 状態の終点に来ている場合true
   */
  public function IsStateEnd(DocumentCharacterItor &$documentItor)
  {
    if($this->_currentRule === NULL) {
      return false;
    }
    $endString = $this->_currentRule->GetTail();
    if($endString === '') {
      return false;
    }
    
    $criteria = $documentItor->SubString(0, strlen($endString));
    if(strcmp($endString, $criteria) == 0) {
      return true;
    }
    return false;
  }
  
  
  /**
   * 現在のドキュメント位置がエスケープ文字列上にあるかを返す
   * @return boolean 状態の終点に来ている場合true
   */
  public function IsEscape(DocumentCharacterItor &$documentItor)
  {
    if($this->_currentRule === NULL) {
      return false;
    }
    $escapeString = $this->_currentRule->GetEscape();
    //エスケープ文字列とマッチするか比較する
    $criteria = $documentItor->SubString(0, strlen($escapeString));
    if($escapeString != '' && strcmp($escapeString, $criteria) == 0) {
      return true;
    }
    
    return false;
  }
}

?>