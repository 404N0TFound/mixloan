<?php
session_start();
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC; 
class Xuan_mixloan_Cache
{
  private $codes = '';
  function __construct()
  {
    $code = '0-1-2-3-4-5-6-7-8-9-A-B-C-D-E-F-G-H-I-J-K-L-M-N-O-P-Q-R-S-T-U-V-W-X-Y-Z';
    $codeArray = explode('-',$code);
    shuffle($codeArray);
    $this->codes = implode('',array_slice($codeArray,0,4));
  }
  public function CreateImg()
  {
    $_SESSION['check_pic'] = $this->codes;
    $img = imagecreate(70,25);
    imagecolorallocate($img,0,136,238);
    $testcolor1 = imagecolorallocate($img,255,255,255);
    $testcolor2 = imagecolorallocate($img,255,255,255);
    $testcolor3 = imagecolorallocate($img,255,255,255);
    $testcolor4 = imagecolorallocate($img,255,255,255);
    for ($i = 0; $i < 4; $i++)
    {
      imagestring($img,rand(5,6),8 + $i * 15,rand(2,8),$this->codes[$i],rand(1,4));
    }
    $res = imagegif($img,IA_ROOT."/addons/xuan_mixloan/data/cache/".md5($this->codes).".gif");
    return $this->codes;
  }
}