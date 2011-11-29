<?php

/**
 * テキストの背景色/文字色/強調等のスタイル情報を保持するクラス
 */
class Style
{
  protected $_id;
  protected $_backgroundColor;
  protected $_fontColor;
  protected $_bold;
  protected $_italic;
  
  public function __construct($id, $back, $font, $bold, $italic)
  {
    $this->_id = $id;
    $this->_backgroundColor = $back;
    $this->_fontColor = $font;
    $this->_bold = $bold;
    $this->_italic = $italic;
  }
  
  
  /**
   * 現在のスタイルを指定されたスタイルで上書きします。
   * (値に"inherit"が指定された場合は上書きしません)
   */
  public function MergeStyle(Style $style)
  {
    if($style->GetBackgroundColor() != 'inherit') {
      $style->_backgroundColor = $style->GetBackgroundColor();
    }
    if($style->GetFontColor() != 'inherit') {
      $style->_fontColor = $style->GetFontColor();
    }
    if($style->IsBold() != 'inherit') {
      $style->_bold = $style->IsBold();
    }
    if($style->IsItalic() != 'inherit') {
      $style->_italic = $style->IsItalic();
    }
  }
  
  function GetId()
  { return $this->_id; }
  
  function GetBackgroundColor()
  { return $this->_backgroundColor; }
  
  function GetFontColor()
  { return $this->_fontColor; }

  function IsBold()
  { return $this->_bold; }

  function IsItalic()
  { return $this->_italic; }
}

?>