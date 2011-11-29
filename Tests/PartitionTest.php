<?php

require_once 'PHPUnit/FrameWork.php';
require_once dirname(__FILE__) . '/common.php';
require_once dirname(__FILE__) . '/../classes/Document.class.php';
require_once dirname(__FILE__) . '/../classes/LanguageManager.class.php';
require_once dirname(__FILE__) . '/../classes/Partition.class.php';


class PartitionTest extends PHPUnit_Framework_TestCase
{
  protected $_partition;
  protected $_language;
  
  public function setup()
  {
    $languageManager = &LanguageManager::GetInstance();
    $languageManager->LoadFromXML('language_test.xml');
    
    $this->_language = $languageManager->GetLanguageById('HTML');
    $state = $this->_language->GetStateById('HTML');
    $NULL=NULL;
    $this->_partition = new Partition($NULL, $state, 0, 1000);
  }
  
  public function testAppendChild()
  {
    $NULL=NULL;
    $state = $this->_language->GetStateById('HTML_TAG');
    $newPartition = new Partition($NULL, $state, 10, 20-10);
    $this->_partition->AppendChild($newPartition);
    
    $this->assertEquals(1, $this->_partition->GetChildrenCount());
    
    $newPartition = new Partition($NULL, $state, 21, 30-21);
    $this->_partition->AppendChild($newPartition);
    $this->assertEquals(2, $this->_partition->GetChildrenCount());
  }
  
  
  public function testRemoveNode()
  {
    $NULL=NULL;
    //追加して削除
    $state = $this->_language->GetStateById('HTML_TAG');
    $newPartition = new Partition($NULL, $state, 10, 20-10);
    $this->_partition->AppendChild($newPartition);
    $this->_partition->RemoveNode($newPartition);
    
    $this->assertEquals(0, $this->_partition->GetChildrenCount());
    
    //同じ値の要素を2つ追加して、片方の要素で2回削除。
    //片方は削除されずに残るはず。
    $newPartition = new Partition($NULL, $state, 10, 20-10);
    $newPartition2 = new Partition($NULL, $state, 10, 20-10);
    $this->_partition->AppendChild($newPartition);
    $this->_partition->AppendChild($newPartition2);
    $this->_partition->RemoveNode($newPartition);
    $this->_partition->RemoveNode($newPartition);
    $this->assertEquals(1, $this->_partition->GetChildrenCount());
    
    //追加してもいない要素を渡しても何も起きない
    $newPartition3 = new Partition($NULL, $state, 20, 30-20);
    $this->_partition->RemoveNode($newPartition3);
    $this->assertEquals(1, $this->_partition->GetChildrenCount());
    
    //一度全部削除
    $this->_partition->RemoveNode($newPartition2);
    $this->assertEquals(0, $this->_partition->GetChildrenCount());
    
    //2つ追加して2つ削除したら0になるか
    $newPartition = new Partition($NULL, $state, 10, 20-10);
    $newPartition2 = new Partition($NULL, $state, 10, 20-10);
    $this->_partition->AppendChild($newPartition);
    $this->_partition->AppendChild($newPartition2);
    $this->_partition->RemoveNode($newPartition);
    $this->_partition->RemoveNode($newPartition2);
    $this->assertEquals(0, $this->_partition->GetChildrenCount());
  }
  
  
  public function testGetChildNodeAt()
  {
    $NULL=NULL;
    $state = $this->_language->GetStateById('HTML_TAG');
    $newPartition = new Partition($NULL, $state, 10, 20-10);
    $newPartition2 = new Partition($NULL, $state, 21, 50-21);
    $newPartition3 = new Partition($NULL, $state, 30, 35-30);
    $newPartition2->AppendChild($newPartition3);
    $this->_partition->AppendChild($newPartition);
    $this->_partition->AppendChild($newPartition2);
    
    //同じ型だがインスタンスの異なるPartitionクラスは別物とみなされる事を確認
    $this->assertFalse($newPartition === $newPartition2);
    
    //単純な取得処理
    $found = $this->_partition->GetChildNodeAt(1);
    $this->assertTrue($found === $this->_partition);
    
    //特定の子ノードを取得
    $found = $this->_partition->GetChildNodeAt(11);
    $this->assertTrue($found === $newPartition);
    
    $found = $this->_partition->GetChildNodeAt(25);
    $this->assertTrue($found === $newPartition2);
    
    $found = $this->_partition->GetChildNodeAt(32);
    $this->assertTrue($found === $newPartition3);
    
    //どれにも当てはまらない
    $found = $this->_partition->GetChildNodeAt(300000);
    $this->assertNull($found);
  }
  
  
  public function testIsInclude()
  {
    $state = $this->_language->GetStateById('HTML');
    $NULL=NULL;

    //指定された点がパーティションの手前にある場合はFALSE
    $this->assertFalse($this->_partition->IsInclude(-10));
    
    //指定された点がパーティションの中にある場合はTRUE
    $this->assertTrue($this->_partition->IsInclude(10));
    
    //指定された点がパーティションの後にある場合はFALSE
    $this->assertFalse($this->_partition->IsInclude(4000));
  }
  
  
  public function testIsOn()
  {
    $state = $this->_language->GetStateById('HTML');
    $NULL=NULL;

    $partiotion = new Partition($NULL, $state, 1, 30-1);
    $partiotion2 = new Partition($NULL, $state, 10, 20-10);
    $partiotion3 = new Partition($NULL, $state, 35, 50-35);
    
    $partiotion->AppendChild($partiotion2);
    $this->_partition->AppendChild($partiotion);
    $this->_partition->AppendChild($partiotion3);
    
    //指定された点を含むノードが子ノードの中に存在する場合はFALSEになる事を確認
    $this->assertFalse($this->_partition->IsOn(5));
    
    //指定された点が子ノードには含まれていない場合はTRUEになる事を確認
    $this->assertTrue($this->_partition->IsOn(33));
    
    //TODO: 範囲外の場合はNULLが返される事を確認
    $this->assertFalse($this->_partition->IsOn(4000));
  }
  
  
  public function testSetParent()
  {
    $state = $this->_language->GetStateById('HTML');
    $NULL=NULL;
    $partition = new Partition($NULL, $state, 0, 1000);
    $partition2 = new Partition($NULL, $state, 0, 1000);
    $child = new Partition($NULL, $state, 0, 1000);
    
    $partition->appendChild($child);
    //partitionの下にchildがいる事を確認
    $this->assertEquals(1, $partition->GetChildrenCount());
    $this->assertEquals(0, $partition2->GetChildrenCount());
    
    //partition2にchildが移った事を確認
    $child->SetParent($partition2);
    $this->assertTrue($partition2 === $child->GetParent());
    $this->assertEquals(0, $partition->GetChildrenCount());
    
    
  }
  
  
  public function testRemovePartitionsAfterPosition()
  {
    $this->buildTestTree();
    
    //削除前の確認
    $this->AssertEquals(3, $this->_partition->GetChildrenCount());
    
    //2番目の子ノードを取得して子要素の数をカウント
    $childNode = $this->_partition->GetChildNodeAt(21);
    $this->AssertEquals(4, $childNode->GetChildrenCount());
    
    $childNode2 = $this->_partition->GetChildNodeAt(30);
    $this->AssertFalse($childNode2->IsOn(42));
    
    //P4から下を全て削除
    $this->_partition->RemovePartitionsAfterPosition(42);
    $this->AssertEquals(2, $this->_partition->GetChildrenCount());
    $this->AssertEquals(1, $childNode->GetChildrenCount());
    
    //もう一度削除するとP3が消える
    $this->_partition->RemovePartitionsAfterPosition(33);
    $this->AssertEquals(0, $childNode->GetChildrenCount());
    
    //もう一度削除するとP2が消える
    $this->_partition->RemovePartitionsAfterPosition(42);
    $this->AssertEquals(1, $this->_partition->GetChildrenCount());
    
    //もう一度削除するとP1が消える
    $this->_partition->RemovePartitionsAfterPosition(15);
    $this->AssertEquals(0, $this->_partition->GetChildrenCount());
    
    //もう一度削除すると初期化される
    $this->_partition->RemovePartitionsAfterPosition(42);
    $this->assertNull($this->_partition->GetParent());
    $this->AssertEquals(0, $this->_partition->GetLength());
    
    //もう一度ツリーを構築しなおす
    $this->buildTestTree();
    
    //削除前の確認
    $this->AssertEquals(3, $this->_partition->GetChildrenCount());
    //2番目の子ノードを取得して子要素の数をカウント
    $childNode = $this->_partition->GetChildNodeAt(21);
    $this->AssertEquals(4, $childNode->GetChildrenCount());
    
    //範囲外を削除した場合は何も起きない
    $this->_partition->RemovePartitionsAfterPosition(4000);
    $this->AssertEquals(3, $this->_partition->GetChildrenCount());
    $this->AssertEquals(4, $childNode->GetChildrenCount());
    
    
  }
  
  
  protected function buildTestTree()
  {
    $state = $this->_language->GetStateById('HTML');
    $NULL=NULL;
    $this->_partition = new Partition($NULL, $state, 0, 1000);
    
    $NULL=NULL;
    $state = $this->_language->GetStateById('HTML_TAG');
    //Root
    //  P1
    //  P2
    //    P3
    //    P4
    //    P5
    //      P6
    //    P7
    //  P8
    $newPartition = new Partition($NULL, $state, 10, 20-10);
    $newPartition2 = new Partition($NULL, $state, 21, 100-21);
    $newPartition3 = new Partition($NULL, $state, 30, 35-30);
    $newPartition4 = new Partition($NULL, $state, 40, 45-40);
    $newPartition5 = new Partition($NULL, $state, 50, 80-50);
    $newPartition6 = new Partition($NULL, $state, 55, 60-55);
    $newPartition7 = new Partition($NULL, $state, 90, 95-90);
    $newPartition8 = new Partition($NULL, $state, 101, 150-101);
    $newPartition5->AppendChild($newPartition6);
    $newPartition2->AppendChild($newPartition3);
    $newPartition2->AppendChild($newPartition4);
    $newPartition2->AppendChild($newPartition5);
    $newPartition2->AppendChild($newPartition7);
    $this->_partition->AppendChild($newPartition);
    $this->_partition->AppendChild($newPartition2);
    $this->_partition->AppendChild($newPartition8);
  }
  
  
  public function testUpdate()
  {
    $source = file_get_contents('./test_source1.html');
    
    $document = new Document();
    $document->SetContent($source);
    $documentItor = &$document->GetDocumentItor(0, 1000);
    
    //簡単なソースを解析させた結果を判定する
    $this->_partition->Update($documentItor);
    
    //ルートパーティションの途中の文字を指定してノードを取得。正しく自分自身を取得できるか
    $testNode = $this->_partition->GetChildNodeAt(7);
    $this->assertTrue($testNode === $this->_partition);
    //ルートパーティションの途中の文字を指定してノードを取得。正しく自分自身を取得できるか
    $testNode = $this->_partition->GetChildNodeAt(6);
    $this->assertTrue($testNode === $this->_partition);
    
    $this->assertEquals(6, $this->_partition->GetChildrenCount());
    $firstNode = &$this->_partition->GetChildNodeAt(1);
    $this->assertEquals(0, $firstNode->GetOffset());
    $this->assertEquals(6, $firstNode->GetLength());
    $this->assertEquals('<', $firstNode->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals(0, $firstNode->GetChildrenCount());
    
    //IMGタグ内の文字列を検証
    $node = &$this->_partition->GetChildNodeAt(50);
    //echo "\n" . $node->dumpPartitionStructure();die;
    $this->assertEquals(48, $node->GetOffset());
    $this->assertEquals(47, $node->GetLength());
    $this->assertEquals('<', $node->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals(2, $node->GetChildrenCount());
    
    
    //エスケープを含むルールの解析
    $source = '<?php  echo "Test\"String"; ?>';
    $document->SetContent($source);
    $documentItor = &$document->GetDocumentItor(0, 1000);
    $this->_partition->Update($documentItor);
    $node = &$this->_partition->GetChildNodeAt(14);
    $this->assertEquals('"', $node->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals('\"', $node->GetState()->GetCurrentRule()->GetEscape());
    $this->assertEquals(14, $node->GetLength());

    //行コメントの解析(改行=LF)
    $source = '<?php  //aaaaaaaa'."\n"
            . 'test ?>';
    $document->SetContent($source);
    $documentItor = &$document->GetDocumentItor(0, 1000);
    $this->_partition->Update($documentItor);
    
    $node = &$this->_partition->GetChildNodeAt(8);
    $this->assertEquals('//', $node->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals('/', $node->GetState()->GetCurrentRule()->GetEscape());
    $this->assertEquals(0, $node->GetChildrenCount());
    $this->assertEquals(7, $node->GetOffset());
    $this->assertEquals(11, $node->GetLength());
    
    //2行目の文字列は行コメントではない
    $node = &$this->_partition->GetChildNodeAt(20);
    $this->assertEquals('<?php', $node->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals('?>', $node->GetState()->GetCurrentRule()->GetTail());
    $this->assertEquals(1, $node->GetChildrenCount());
    $this->assertEquals(0, $node->GetOffset());
    
    //行コメントの解析(改行=CRLF)
    $source = '<?php  //aaaaaaaa'."\r\n"
            . 'test ?>';
    $document->SetContent($source);
    $documentItor = &$document->GetDocumentItor(0, 1000);
    $this->_partition->Update($documentItor);
    
    $node = &$this->_partition->GetChildNodeAt(8);
    $this->assertEquals('//', $node->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals('/', $node->GetState()->GetCurrentRule()->GetEscape());
    $this->assertEquals(0, $node->GetChildrenCount());
    $this->assertEquals(7, $node->GetOffset());
    $this->assertEquals(12, $node->GetLength());
    
    //行コメントの解析(改行=CR)
    $source = '<?php  //aaaaaaaa'."\r"
            . 'test ?>';
    $document->SetContent($source);
    $documentItor = &$document->GetDocumentItor(0, 1000);
    $this->_partition->Update($documentItor);
    
    $node = &$this->_partition->GetChildNodeAt(8);
    $this->assertEquals('//', $node->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals('/', $node->GetState()->GetCurrentRule()->GetEscape());
    $this->assertEquals(0, $node->GetChildrenCount());
    $this->assertEquals(7, $node->GetOffset());
    $this->assertEquals(11, $node->GetLength());
    
  }
  
  
  public function testUpdate2()
  {
    $source = file_get_contents('./test_source3.php');
    
    $document = new Document();
    $document->SetContent($source);
    $documentItor = &$document->GetDocumentItor(0, 1000);
    
    //最初は通常通り解析を行う
    $this->_partition->Update($documentItor);
    
    $this->assertEquals(4, $this->_partition->GetChildrenCount());
    
    //Aタグ内には文字列ノードが含まれている
    $node = &$this->_partition->GetChildNodeAt(7);
    $this->assertEquals(6, $node->GetOffset());
    $this->assertEquals(72, $node->GetLength());
    $this->assertEquals('<', $node->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals(1, $node->GetChildrenCount());
    
    //PHPタグ内には3つのノードが含まれている
    $node = &$this->_partition->GetChildNodeAt(27);
    $this->assertEquals(27, $node->GetOffset());
    $this->assertEquals(49, $node->GetLength());
    $this->assertEquals('<?php', $node->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals(3, $node->GetChildrenCount());
    
    //ドキュメント中の一部の文字列をカットしても正しく解析できる
    //(マルチラインコメント(/*)の*を削除した場合)
    $cuttedSource = substr($source, 0, 45) . substr($source, 46, strlen($source)-46);
    $document->SetContent($cuttedSource);
    $documentItor = &$document->GetDocumentItor(45, 1000);
    $this->_partition->Update($documentItor);

    //echo "\n" . $this->_partition->dumpPartitionStructure() . "\n";
    
    $this->AssertEquals(96, $this->_partition->GetLength());
    //PHPタグ内には1つのノードが含まれており、終点は95
    $node = &$this->_partition->GetChildNodeAt(28);
    $this->assertEquals(27, $node->GetOffset());
    $this->assertEquals(94, $node->GetEndPosition());
    $this->assertEquals('<?php', $node->GetState()->GetCurrentRule()->GetHead());
    $this->assertEquals(1, $node->GetChildrenCount());
    //マルチラインコメントの長さは54バイト
    $node = &$this->_partition->GetChildNodeAt(33);
    $this->assertEquals(54, $node->GetLength());
    //Aタグの終点も95
    $node = &$this->_partition->GetChildNodeAt(7);
    $this->assertEquals(94, $node->GetEndPosition());
    
    //ROOTノードの終点も95
    $this->assertEquals(95, $this->_partition->GetEndPosition());
  }
}

?>