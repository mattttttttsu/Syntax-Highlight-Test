<?php

require_once dirname(__FILE__) . '/State.class.php';
require_once dirname(__FILE__) . '/Keyword.class.php';

/**
 * 任意の言語の状態(HTML,HTML_Commentなど)、ルール(状態遷移の条件)、
 * キーワード(タグ、function,forなど）などの一覧や初期状態を保持するクラス
 */
class Language
{
  protected $_stateMap;
  protected $_ruleMap;
  protected $_keywordMap;
  protected $_styleMap;
  protected $_initialState;
  protected $_id;
  
  public function __construct()
  {
    $this->_id = '';
    $this->_stateMap = NULL;
    $this->_ruleMap = NULL;
    $this->_keywordMap = NULL;
    $this->_styleMap = NULL;
    $this->_initialState = '';
  }
  
  
  /**
   * 配列情報からオブジェクトを初期化する
   * @param array $array 言語設定を含んだ連想配列
   *
   * ここで渡される配列はXML_Unserializerをオプション
   * "parseAttributes","attributesArray"で初期化した時の
   * フォーマットを扱う事を前提としています。
   */
  public function InitByArray($array)
  {
    //Languageタグの解析
    $this->_id = $array[1]['id'];
    $this->_initialState = $array[1]['initial_state'];
    $stateElements = (array)$array['State'];
    
    //Stateタグの解析
    foreach($stateElements as $stateElement)
    {
      $stateId = $stateElement[1]['id'];
      $styleId = $stateElement[1]['style_id'];
      $ruleElements = (array)$stateElement['Rule'];
      $keywordElements = (array)$stateElement['Keywords']['Keyword'];
      
      //Ruleタグの解析
      foreach($ruleElements as $ruleElement)
      {
        $rule = new Rule($ruleElement[1]['next'], $ruleElement[1]['head'], $ruleElement[1]['tail'],
                         $ruleElement[1]['escape'], $ruleElement[1]['stop_with_eol']);
        
        $this->_ruleMap[$stateId][] = $rule;
      }
      
      //Keywordタグの解析
      foreach($keywordElements as $keywordElement)
      {
        $words = explode(',', $keywordElement['_content']);
        $wordStyleId = $keywordElement[1]['style_id'];
        foreach($words as $word)
        {
          $word = trim($word);
          $keyword = new Keyword($word, $wordStyleId);
          $this->_keywordMap[$stateId][$word] = $keyword;
        }
      }
      
      $state = new State($this, $stateId, $styleId);
      $this->_stateMap[$stateId] = $state;
    }
    
    //スタイル情報の解析
    $styleElements = (array)$array['Styles']['Style'];
    foreach($styleElements as $styleElement)
    {
      $style = new Style($styleElement[1]['id'], $styleElement[1]['background_color'],
                         $styleElement[1]['font_color'], $styleElement[1]['bold'], $styleElement[1]['italic']);
      
      $this->_styleMap[ $styleElement[1]['id'] ] = $style;
    }
  }
  
  public function GetId()
  { return $this->_id; }
  
  public function GetInitialState()
  { return $this->_initialState; }
  
  public function GetStateById($id)
  {
    if(!isset($this->_stateMap[$id])) {
      return NULL;
    }
    return $this->_stateMap[$id];
  }
  
  public function GetStyleById($id)
  {
    if(!isset($this->_styleMap[$id])) {
      return NULL;
    }
    return $this->_styleMap[$id];
  }
  
  public function GetRuleListByStateId($stateId)
  {
    if(!isset($this->_ruleMap[$stateId])) {
      return NULL;
    }
    return $this->_ruleMap[$stateId];
  }

  public function GetKeywordListByStateId($stateId)
  {
    if(!isset($this->_keywordMap[$stateId])) {
      return NULL;
    }
    return $this->_keywordMap[$stateId];
  }

}

?>