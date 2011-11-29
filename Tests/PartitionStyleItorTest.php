<?php

require_once 'PHPUnit/FrameWork.php';
require_once dirname(__FILE__) . '/common.php';
require_once dirname(__FILE__) . '/../classes/Document.class.php';
require_once dirname(__FILE__) . '/../classes/LanguageManager.class.php';
require_once dirname(__FILE__) . '/../classes/Partition.class.php';
require_once dirname(__FILE__) . '/../classes/PartitionStyleItor.class.php';


class PartitionStyleItorTest extends PHPUnit_Framework_TestCase
{
  protected $_styleItor;
  protected $_partition;
  protected $_language;
  
  public function setup()
  {
    $languageManager = &LanguageManager::GetInstance();
    $languageManager->LoadFromXML('language_test.xml');
    
    $this->_language = $languageManager->GetLanguageById('HTML');
    $state = $this->_language->GetStateById($this->_language->GetInitialState());
    $NULL=NULL;
    $this->_partition = new Partition($NULL, $state, 0, 0);
    
    $document = new Document();
    $source = file_get_contents(dirname(__FILE__) . '/test_source1.html');
    $document->SetContent($source);
    
    $documentItor = $document->GetDocumentItor(0, 1000);
    $this->_partition->Update($documentItor);
  }
  
  public function testIteration()
  {
    $this->_styleItor = new PartitionStyleItor($this->_partition, 0, 1000);
    //この時点でイテレーションは終了しない
    $this->assertFalse($this->_styleItor->IsEnd());

    //最初の6回はHTML_TAGスタイルを返す
    //その時のパーティションは<HTML>タグのパーティション
    $criteria = $this->_partition->GetChildNodeAt(0);
    $style = NULL;
    for($i=0; $i<6; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->_styleItor->Next();
    }
    
    //次の2回はHTMLスタイルを返す
    //その時のパーティションはルートパーティション
    $criteria = $this->_partition;
    $style = NULL;
    for($i=0; $i<2; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->assertEquals('HTML', $style->GetId());
      $this->_styleItor->Next();
    }
    
    //次の6回はHTML_TAGスタイルを返す
    //その時のパーティションは<BODY>タグのパーティション
    $criteria = $this->_partition->GetChildNodeAt(8);
    $style = NULL;
    for($i=0; $i<6; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の2回はHTMLスタイルを返す
    //その時のパーティションは<BODY>タグのパーティション
    $criteria = $this->_partition;
    $style = NULL;
    for($i=0; $i<2; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の30回はHTML_COMMENTスタイルを返す
    //その時のパーティションはHTML_COMMENTのパーティション
    $criteria = $this->_partition->GetChildNodeAt(16);
    $style = NULL;
    for($i=0; $i<30; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_COMMENT', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //この時点でイテレーションは終了しない
    $this->assertFalse($this->_styleItor->IsEnd());
    
    //次の2回はHTMLスタイルを返す
    //その時のパーティションはルートパーティション
    $criteria = $this->_partition;
    $style = NULL;
    for($i=0; $i<2; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の9回はHTML_TAGスタイルを返す
    //その時のパーティションは<IMG>タグのパーティション
    $criteria = $this->_partition->GetChildNodeAt(48);
    $style = NULL;
    for($i=0; $i<9; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の10回はHTML_STRINGスタイルを返す
    //その時のパーティションはHTML_STRINGのパーティション
    $criteria = $this->_partition->GetChildNodeAt(57);
    $style = NULL;
    for($i=0; $i<10; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_STRING', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の9回はHTMLスタイルを返す
    //その時のパーティションはルートパーティション
    $criteria = $this->_partition->GetChildNodeAt(48);
    $style = NULL;
    for($i=0; $i<9; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の16回はHTML_STRINGスタイルを返す
    //その時のパーティションはHTML_STRINGのパーティション
    $criteria = $this->_partition->GetChildNodeAt(76);
    $style = NULL;
    for($i=0; $i<16; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_STRING', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の3回はHTML_TAGスタイルを返す
    //その時のパーティションはIMGタグのパーティション
    $criteria = $this->_partition->GetChildNodeAt(48);
    $style = NULL;
    for($i=0; $i<3; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の7回はHTML_TAGスタイルを返す
    //その時のパーティションはBODYタグのパーティション
    $criteria = $this->_partition->GetChildNodeAt(95);
    $style = NULL;
    for($i=0; $i<7; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の2回はHTMLスタイルを返す
    //その時のパーティションはルートパーティション
    $criteria = $this->_partition;
    $style = NULL;
    for($i=0; $i<2; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の7回はHTMLスタイルを返す
    //その時のパーティションはHTMLタグのパーティション
    $criteria = $this->_partition->GetChildNodeAt(104);
    $style = NULL;
    for($i=0; $i<7; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //この時点でイテレーションが終了する
    $this->assertTrue($this->_styleItor->IsEnd());
  }


  public function testIteration2()
  {
    //イテレーションを10文字で終了させる場合
    $this->_styleItor = new PartitionStyleItor($this->_partition, 0, 10);
    //この時点でイテレーションは終了しない
    $this->assertFalse($this->_styleItor->IsEnd());

    //最初の6回はHTML_TAGスタイルを返す
    //その時のパーティションは<HTML>タグのパーティション
    $criteria = $this->_partition->GetChildNodeAt(0);
    $style = NULL;
    for($i=0; $i<6; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->_styleItor->Next();
    }
    
    
    //次の2回はHTMLスタイルを返す
    //その時のパーティションはルートパーティション
    $criteria = $this->_partition;
    $style = NULL;
    for($i=0; $i<2; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->assertEquals('HTML', $style->GetId());
      $this->_styleItor->Next();
    }
    
    //次の6回はHTML_TAGスタイルを返す
    //その時のパーティションは<BODY>タグのパーティション
    //この途中でイテレーションが終了する
    $criteria = $this->_partition->GetChildNodeAt(8);
    $style = NULL;
    for($i=0; $i<2; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //この時点でイテレーションが終了する
    $this->assertTrue($this->_styleItor->IsEnd());
  }

  public function testIteration3()
  {
    //イテレーションを途中から開始する場合
    $this->_styleItor = new PartitionStyleItor($this->_partition, 5, 20);
    
    //この時点でイテレーションは終了しない
    $this->assertFalse($this->_styleItor->IsEnd());
    
    //最初の1回はHTML_TAGスタイルを返す
    //その時のパーティションは<HTML>タグのパーティション
    $criteria = $this->_partition->GetChildNodeAt(0);
    $style = NULL;
    for($i=0; $i<1; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->_styleItor->Next();
    }
    
    
    //次の2回はHTMLスタイルを返す
    //その時のパーティションはルートパーティション
    $criteria = $this->_partition;
    $style = NULL;
    for($i=0; $i<2; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->assertEquals('HTML', $style->GetId());
      $this->_styleItor->Next();
    }
    
    //次の6回はHTML_TAGスタイルを返す
    //その時のパーティションは<BODY>タグのパーティション
    $criteria = $this->_partition->GetChildNodeAt(8);
    $style = NULL;
    for($i=0; $i<6; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_TAG', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の2回はHTMLスタイルを返す
    //その時のパーティションは<BODY>タグのパーティション
    $criteria = $this->_partition;
    $style = NULL;
    for($i=0; $i<2; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //次の30回はHTML_COMMENTスタイルを返す
    //その時のパーティションはHTML_COMMENTのパーティション
    //この途中でイテレーションが終了する
    $criteria = $this->_partition->GetChildNodeAt(16);
    $style = NULL;
    for($i=0; $i<9; $i++)
    {
      $style = $this->_styleItor->GetCurrent();
      $this->assertEquals('HTML_COMMENT', $style->GetId());
      $this->assertTrue($this->_styleItor->GetCurrentPartition() === $criteria);
      $this->_styleItor->Next();
    }
    
    //この時点でイテレーションが終了する
    $this->assertTrue($this->_styleItor->IsEnd());
  }
  
}

?>