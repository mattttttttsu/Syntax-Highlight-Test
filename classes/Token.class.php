<?php

class Token
{
  protected $_offset = 0;
  protected $_length = 0;
  protected $_style = 0;
  protected $_content = '';
  
  public function __construct($newOffset, $newLength, $newStyle, $newContent)
  {
    $this->_offset = $newOffset;
    $this->_length = $newLength;
    $this->_style = $newStyle;
    $this->_content = $newContent;
  }
  
  
  public function GetOffset()
  { return $this->_offset; }

  public function GetLength()
  { return $this->_length; }

  public function GetStyleId()
  { return $this->_style; }

  public function GetContent()
  { return $this->_content; }

  
  public function GetEndPosition()
  { return $this->_offset + $this->_length - 1; }

  /**
   * トークンが指定された点を含んでいるかどうかを返します。
   */
  public function IsInclude($position)
  {
    $result = (boolean)($this->_offset <= $position && $this->_offset + $this->_length-1 >= $position);
    return $result;
  }
}

?>