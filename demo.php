<?php
//HTMLとPHPが混在したソースを個別にハイライトしてHTML出力するサンプル

ini_set('error_reporting', E_ALL  ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', 1);
ini_set('include_path', dirname(__FILE__) . '/classes/PEAR' . PATH_SEPARATOR . ini_get('include_path'));

require_once dirname(__FILE__) . '/classes/Document.class.php';
require_once dirname(__FILE__) . '/classes/LanguageManager.class.php';
require_once dirname(__FILE__) . '/classes/Partition.class.php';
require_once dirname(__FILE__) . '/classes/PartitionStyleItor.class.php';

//言語設定情報のロード
$languageManager = &LanguageManager::GetInstance();
$languageManager->LoadFromXML('Rules.xml');

//初期のモードとしてHTMLを指定
$language = $languageManager->GetLanguageById('HTML');
$state = $language->GetStateById($language->GetInitialState());

//パーティション(ソースのどこからどこまでがどの状態にあるかの情報)の初期化
$NULL=NULL;
$partition = new Partition($NULL, $state, 0, 0);

//ソースコードを設定し、パーティション情報を初期化
$source = file_get_contents('test_source.php');
$document = new Document();
$document->SetContent($source);
$documentItor = &$document->GetDocumentItor(0, strlen($source));
$partition->Update($documentItor);

//トークンに分解したドキュメントと、対応するスタイルを列挙していく
$start = 0;
$styleItor = new PartitionStyleItor($partition, $start, 2000);
$documentItor = &$document->GetDocumentItor($start, 2000);
while(!$documentItor->IsEnd())
{
  $style = $styleItor->GetCurrent();
  if($style === NULL) {
    $style = new Style('ERROR', '#dddddd', '#000000', false, false);
  }
  
  $currentChar = $documentItor->GetCurrent();
  $currentChar = str_replace(' ', '　', $currentChar);
  
  if($currentChar == "\n" || $currentChar == "\r") {
    echo '<br clear="all" />'."\n";
    $styleItor->Next();
    $documentItor->Next();
    continue;
  }
  
  $boldStyle='';
  if($style->IsBold()) {
    $boldStyle = 'font-weight:bold;';
  }
  
  printf('<div style="display: block; float: left; background-color:%s; color:%s;%s">%s</div>',
         $style->GetBackgroundColor(), $style->GetFontColor(), $boldStyle, $currentChar);
  
  $styleItor->Next();
  $documentItor->Next();
}
?>