<?php
/*
 * PukiWiki - recentdetail.inc.php
 * �ǿ���?���ɽ������ץ饰���� - �����λ����̤˥��롼��ɽ��
 * based on "recent.inc.php" by Y.MASUI��
 *
 * Ver.1.1 2004.5.7
 * CopyRight 2004 Yuichirot (GPL2)
 */

function plugin_recentdetail_convert()
{
  global $script,$BracketName,$date_format;
  global $_recent_plugin_frame;

  $recent_lines = 10;   // ɽ�����[��]
  $border = array(60,60*24,60*24*7); // �ƥ��롼�פζ���[ʬ]
  $border_cap = array('1����','1��','1����');

  $showdate_border = 60 * 24; // ����ɽ���ζ���[ʬ]

  $argcount = func_num_args();
  $border_arg = array();

	
  if ($argcount>0) {
    $args = func_get_args();
    if (is_numeric($args[0])) {
      $recent_lines = $args[0];
    }
	  
   		
    for($j=1;$j<$argcount;$j++) {
      $buf='';
      $cap='';
      $total = 0;

      $str = $args[$j];

      for ($i = 0; $i < strlen($str); ++$i) {
	$c = ord($str[$i]);
	if ($c >= ord('0') && $c <= ord('9')) {
	  $buf = $buf . $str[$i];
	} else {
	  if($c == ord('m')) {
	    $total = $total + (int)$buf;
	    $cap .= $buf.'ʬ';
	  } else if($c == ord('h')) {
	    $total = $total + (int)$buf * 60;
	    $cap .= $buf.'����';
	  } else if($c == ord('d')) {
	    $total = $total + (int)$buf * 60 * 24;
	    $cap .= $buf.'��';
	  } else if($c == ord('w')) {
	    $total = $total + (int)$buf * 60 * 24 * 7;
	    $cap .= $buf.'����';
	  }
	    
	  $buf = "";
	}
      }

      if($total>0) {
	$border_arg[] = $total; 
	$border_cap_arg[] = $cap;
      }
    }
  }

  if(count($border_arg)>0) {
    $border = $border_arg;
    $border_cap = $border_cap_arg;
  }

  $date = $items = '';
  if (!file_exists(CACHE_DIR.'recent.dat')) {
    return '';
  }
  $recent = file(CACHE_DIR.'recent.dat');
  $lines = array_splice($recent,0,$recent_lines);

  $borderno = 0;
  $_borderno = -1;
  $inlist = FALSE;
  $border_change = FALSE;

  foreach ($lines as $line) {
    list($time,$page) = explode("\t",rtrim($line));
    $recent_minute = floor(max(0,(UTIME - $time) / 60));

    while ($borderno < count($border)) {
      if($recent_minute <= $border[$borderno])
	break;
      else
	$borderno = $borderno + 1;	      
    }
    if($borderno != $_borderno) {
      if($inlist) $items .= '</ul>';
      if($borderno < count($border))
	$items .= sprintf("<h5>%s����˹���</h5>\n<ul class=\"recent_list\">",$border_cap[$borderno]);
      else
	$items .= "<h5>����¾</h5>\n<ul class=\"recent_list\">";
      $_borderno = $borderno;
      $border_change = TRUE;
      $inlist = TRUE;
    } else {
      $border_change = FALSE;
    }

    // ���դν���
    if($recent_minute > $showdate_border) { // $showdate_borderʬ�ʾ�вᤷ���ڡ����ξ��Τ����դ����
		  
      $_date = get_date($date_format,$time);
      if ($date != $_date or $border_change) {
	$date = $_date;
	$items .= "</ul><strong>$date</strong>\n<ul class=\"recent_list\">";
      }
    }
		
    $s_page = htmlspecialchars($page);
    $r_page = rawurlencode($page);
    $pg_passage = get_pg_passage($page,FALSE);

    if($recent_minute > $showdate_border) {
      $passage = '';
    } else {
      $passage = $pg_passage;
    }

    $items .=" <li><a href=\"$script?$r_page\" title=\"$s_page $pg_passage\">$s_page</a> $passage</li>\n";
  }
  if (count($lines)) {
    $items .='</ul>';
  }
  return sprintf($_recent_plugin_frame,count($lines),$items);
}


?>
