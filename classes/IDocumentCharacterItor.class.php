<?php


/**
 * ドキュメント(バッファ？)の特定の範囲を走査するためのインターフェース
 */
interface IDocumentCharacterItor
{
  
  /**
   * 走査をn文字分進める
   * @param int $amount 進める量
   */
  public function Next($amount=1);
  
  /**
   * 走査をn文字分戻す
   * @param int $amount 戻る量
   */
  public function Prev($amount=1);
  
  /**
   * 現在位置の文字を返す
   * @return string
   */
  public function GetCurrent();
  
  /**
   * 現在の位置を返す
   * @return int 
   */
  public function GetPosition();
  
  /**
   * 始点かを返す
   * @return boolean
   */
  public function IsHead();
  
  /**
   * 終点まで進んだかを返す
   * @return boolean
   */
  public function IsEnd();
  
  /**
   * 指定された範囲の文字列を切り出した結果を返す
   * @return string 切り出した結果
   */
  public function SubString($begin, $length);
  
  public function SetPosition($position);
}

?>