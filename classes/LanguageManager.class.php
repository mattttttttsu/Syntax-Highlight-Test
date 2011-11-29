<?php

require_once 'XML/Unserializer.php';
require_once dirname(__FILE__) . '/Rule.class.php';
require_once dirname(__FILE__) . '/Language.class.php';

/**
 * 様々な言語の一覧を管理するクラス
 */
class LanguageManager
{
  /**
   * 言語の一覧
   * @var Array(Language)
   */
  protected $_languageMap;
  
  
  public static function &GetInstance()
  {
    static $instance = NULL;
    if($instance === NULL) {
      $instance = new LanguageManager();
    }
    return $instance;
  }
  
  
  protected function __construct()
  {
    $this->_languageMap = NULL;
  }
  
  
  /**
   * 指定されたファイルから設定情報をロードします。
   */
  public function LoadFromXML($fileName)
  {
    $unserializer = new XML_Unserializer();
    
    $unserializer->unserialize($fileName, true, array('parseAttributes'=>true,'attributesArray'=>true));
    $data = $unserializer->getUnserializedData();
    
    $language = new Language();
    $language->InitByArray($data);
    $this->_languageMap[$language->GetId()] = $language;
  }
  
  
  public function &GetLanguageById($id)
  {
    if(!isset($this->_languageMap[$id])) {
      return NULL;
    }
    
    return $this->_languageMap[$id];
  }
}

?>