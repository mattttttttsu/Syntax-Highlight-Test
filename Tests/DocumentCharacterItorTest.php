<?php

require_once 'PHPUnit/FrameWork.php';
require_once dirname(__FILE__) . '/common.php';
require_once dirname(__FILE__) . '/../classes/Document.class.php';
require_once dirname(__FILE__) . '/../classes/DocumentCharacterItor.class.php';


class DocumentCharacterItorTest extends PHPUnit_Framework_TestCase
{
  public function setup()
  {
  }
  
  public function testNext()
  {
    //普通の走査
    $document = new Document();
    $content = 'ABCD';
    $document->SetContent($content);
    $documentItor = $document->GetDocumentItor(0,10);
    $documentItor->Next();
    $this->assertEquals('B', $documentItor->GetCurrent());
    
    //2文字進んだ場合
    $documentItor->Next(2);
    $this->assertEquals('D', $documentItor->GetCurrent());
    
    //飛び越えた場合
    $documentItor->Next();
    $this->assertEquals('', $documentItor->GetCurrent());
    
    //マイナスを指定した場合
    $documentItor = $document->GetDocumentItor(0,10);
    $documentItor->Next(-2);
    $this->assertEquals('A', $documentItor->GetCurrent());
    
  }
  
  public function testPrev()
  {
    //普通の走査
    $document = new Document();
    $content = 'ABCD';
    $document->SetContent($content);
    $documentItor = $document->GetDocumentItor(0,10);
    $documentItor->Next(3);

    $documentItor->Prev();
    $this->assertEquals('C', $documentItor->GetCurrent(), 'Current:'.$documentItor->GetCurrent());
    
    //2文字戻った場合
    $documentItor->Prev(2);
    $this->assertEquals('A', $documentItor->GetCurrent());
    
    //飛び越えた場合
    $documentItor->Prev();
    $this->assertEquals('A', $documentItor->GetCurrent());
    
    //マイナスを指定した場合
    $documentItor = $document->GetDocumentItor(0,10);
    $documentItor->Next(3);
    $documentItor->Prev(-2);
    $this->assertEquals('D', $documentItor->GetCurrent());
  }
  

  public function testIsHead()
  {
    //普通の状態
    $document = new Document();
    $content = 'ABCD';
    $document->SetContent($content);
    $documentItor = $document->GetDocumentItor(0,10);
    $this->assertTrue($documentItor->IsHead());
    
    //進んだ場合
    $documentItor->Next();
    $this->assertFalse($documentItor->IsHead());
    
    //イテレーションがドキュメントの途中からの場合
    $documentItor = $document->GetDocumentItor(2,10);
    $this->assertTrue($documentItor->IsHead());
    
    //そこから進んだ場合
    $documentItor->Next();
    $this->assertFalse($documentItor->IsHead());
  }


  public function testIsEnd()
  {
    //普通の状態
    $document = new Document();
    $content = 'ABCD';
    $document->SetContent($content);
    $documentItor = $document->GetDocumentItor(0,10);
    $this->assertFalse($documentItor->IsEnd());
    
    //1文字進んだ場合
    $documentItor->Next();
    $this->assertFalse($documentItor->IsEnd());
    
    $documentItor->Next();
    $this->assertFalse($documentItor->IsEnd());
    
    //終端の1文字手前(最後の1文字)
    $documentItor->Next();
    $this->assertFalse($documentItor->IsEnd());
    
    //更に進んだ場合
    $documentItor->Next();
    $this->assertTrue($documentItor->IsEnd());
    $this->assertEquals($document->GetContentLength(), $documentItor->GetPosition());
    
    
    //イテレーションがドキュメントの途中からの場合
    $documentItor = $document->GetDocumentItor(1,10);
    $this->assertFalse($documentItor->IsEnd());
    
    //そこから進んだ場合
    $documentItor->Next();
    $this->assertFalse($documentItor->IsEnd());
    
    //いきなり終点を超えた場合
    $documentItor = $document->GetDocumentItor(0,10);
    $documentItor->Next(5);
    $this->assertTrue($documentItor->IsEnd());
  }


  public function testSubString()
  {
    //先頭から数文字
    $document = new Document();
    $content = 'ABCDEFGHIJKL';
    $document->SetContent($content);
    $documentItor = $document->GetDocumentItor(0,10);
    $this->assertEquals('ABCD', $documentItor->SubString(0, 4));
    
    //0文字取得
    $this->assertEquals('', $documentItor->SubString(0, 0));
    
    //本文以上の長さを取得
    $this->assertEquals('ABCDEFGHIJKL', $documentItor->SubString(0, 20));
    
    //範囲外の文字列を取得
    $this->assertEquals('', $documentItor->SubString(20, 30));
    
    //始点がマイナス
    $this->assertEquals('ABCDE', $documentItor->SubString(-5, 5));

    //長さがマイナス
    $this->assertEquals('', $documentItor->SubString(5, -10));
  }

}

?>