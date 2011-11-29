<?php

/**
 * ドキュメントを解析し、パーティション情報を作成するクラス
 */
class Scanner
{
  /**
   * ドキュメントを解析した結果のパーティション情報
   * @var Partition
   */
  protected $_partitionTree;
  
  /**
   * 現在の言語
   * @var Language
   */
  protected $_language;
  
  /**
   * 解析対象のドキュメント
   * @var Document
   */
  protected $_document;
  
  public function __construct(Document &$document, Language &$language)
  {
    $this->_language = &$language;
    $this->_document = &$document;
    
    $stateId = $this->_language->GetInitialState();
    $state = &$this->_language->GetStateById($stateId);
    
    $null=NULL;
    $this->_partitionTree = new Partition($null,$state, 0,0);
  }
  
  
  public function Parse($offset, $length)
  {
    $documentItor = &$this->_document->GetDocumentItor(0, 100 * 1000);
    $this->_partitionTree->Update($documentItor);
  }
  
  
  public function GetCharacterItor()
  {
    
  }
}

?>