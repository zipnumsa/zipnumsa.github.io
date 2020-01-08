<?php
// ### º¸°ü¿ë Á¤Àû ÆäÀÌÁö °ü·Ã ¿É¼Ç ###############################################################################
$m_global_encoding = "UTF-8"; // Encoding option
$m_static_flag=1;
$m_force_dynamic_flag=1;

// ### µû¿ÈÇ¥ ¹®ÀÚ¿¡ ´ëÇÑ Ã³¸® ¿É¼Ç ¹× ÀÓ½Ã º¸°ü¿ë Àü¿ª º¯¼ö ¼±¾ð ####################################################
ini_set('magic_quotes_runtime', 0);
global $global_string_temp;
$global_string_temp = "_";
$m_static_auto_replace=0;


// ###################################################################################################################

// ### ¹æ¹®ÀÚ ±â·Ï ·Î±× ³²±è #########################################################################################
// ±âÅ¸µîµî
$from_whom = $HTTP_SERVER_VARS['REMOTE_ADDR']; 
$to_where  = $HTTP_SERVER_VARS['REQUEST_URI'];
$via_what  = $HTTP_SERVER_VARS['HTTP_REFERER'];
if(strlen($from_whom)<1) $from_whome="NAN";
if(strlen($to_where) <1) $to_where  ="NAN";
if(strlen($via_what) <1) $via_what  ="NAN";



$logging   = "AT ".date("Y.m.d, g:i a")." FROM ".$from_whom." TO ".$to_where." VIA ".$via_what."\n";
$fp = fopen("wklog.txt","a");
fwrite($fp, $logging);
fclose($fp);

// ### ÇÔ¼ö ±¸Çö ºÎºÐ ################################################################################################
// ### ÇÑ±Û ÄÚµå¸¦ À§ÇÑ Ã³¸® #######################################
function wikiencode($raw){
	$nonraw = urlencode($raw);
	if(strpos(" ".$raw,"%")>0) $nonraw=$raw;
	return str_replace("%","-",$nonraw);
}

// ### ÁÖ¾îÁø text¿¡¼­ [] ¸µÅ©¸¦ Ã£¾Æ³¿ ############################
function wkfunc_find_links_from_text($text_parameter){
	$j = strlen($text_parameter);
	$return_strs = ":";
	for($i=0;$i<$j;$i++){
		$k=strpos($text_parameter,"[",$i);
		if($k != false)
		{
			$l = strpos($text_parameter,"]",$k);
			$deco_url = substr($text_parameter,$k+1,$l-$k-1);
			if(strpos($deco_url,"://")>0) continue;
			if($deco_url == str_replace(" ","",$deco_url) ) {
			$return_strs = $return_strs.$deco_url."\\";
			}
	
		  $i = $l;
		} else break;
	}

	return explode("\\",substr($return_strs,1,strlen($return_strs) ));
}

// ### history bak ÆÄÀÏ Ã£¾Æ³¿ ################################
function wkfunc_backup_file_find_from_filename($filename_parameter, $n_of_returns_parameter){
	$temporal_return = "";
	$temporal_max_time = 0;
	$dir=opendir("./");
	while($file = readdir($dir)) {
	    if( strpos(" ".$file,$filename_parameter)==1 && strpos($file,".bak")>3 )
		{
		$I = strpos($file,".txt")+4;
		$E = strpos($file,".bak");
		$temporal_time = intval(substr($file,$I,$E-$I));
		$time_str = date("M j, Y, g:i a", $temporal_time ) ;
		$temporal_return = $temporal_return." *    [LINK:".$file."]  ".$time_str."\n";
		if($temporal_time>$temporal_max_time) $temporal_max_time = $temporal_time;
		}
	}
	closedir($dir);
	if($n_of_returns_parameter==1) return $filename_parameter.$temporal_max_time.".bak";
		else return $temporal_return;
}

// ### Á¦¸ñÀ¸·Î ±× Á¦¸ñÀ» °¡Áø µ¥ÀÌÅÍ ÆÄÀÏ¸íÀ» Ã£¾Æ µ¹·ÁÁÖ´Â ÇÔ¼ö ####################################################
function wkfunc_file_find_from_title($title_parameter)
{
	$file_number_counter = 0;
	$file_name_return = "__NONEXISTANCE__";
	$temporal_file_pointer = 0;
	$temporal_title = "";
	$temporal_max = 0;

	if(strpos($title_parameter,".txt")>0 && strpos($title_parameter,"kct")>0){
		$temporal_file_pointer = fopen( $title_parameter, "r" );
		$temporal_title = fgets($temporal_file_pointer);
		fclose( $temporal_file_pointer );
		return $temporal_title;
	}

	if(file_exists("wkctlist.xml")!=false){
	$fp = fopen("wkctlist.xml", "r");
	$tmp_xml_contents = fread( $fp, filesize( "wkctlist.xml" ) );
	fclose( $fp );

		$titles = explode( "</title>", $tmp_xml_contents );
		$filenames = explode( "</file>", $tmp_xml_contents );
		for($l=0;$l<count($titles);$l++){
			$I = strpos($titles[$l],"<title>")+7;
			$title = substr($titles[$l], $I);
			$I = strpos($filenames[$l],"<file>")+6;
			$filename = substr($filenames[$l], $I);
				
			if(strcmp($title,$title_parameter)==0) {
				$filename = str_replace(".xml","",$filename);
				if(file_exists($filename)==true) return $filename;
				}
			}
	}


	$dir=opendir("./");
	while($file = readdir($dir)) {
	    if( strpos($file,"kct")==1 && strpos($file,".xml") <1 && (strpos($file,".txt") != false || strpos($file,".TXT") != false) && strcmp(str_replace(".bak","",$file),$file)==0 && strcmp(str_replace(".BAK","",$file),$file)==0 )
		{
		$file_number_counter++;
		$temporal_file_pointer = fopen( $file, "r" );
		$temporal_title = fgets($temporal_file_pointer);
		fclose( $temporal_file_pointer );

		$temporal_title = str_replace( "\r", "" , $temporal_title );
		$temporal_title = str_replace( "\n", "" , $temporal_title );
		$st = strpos( $file, "kct");
		$ed = strpos( $file, ".txt");
		$temporal_page_number = intval( substr( $file, $st+3, $ed-$st-3) );
		if( $temporal_max < $temporal_page_number ) $temporal_max = $temporal_page_number;

		if(strcmp($temporal_title,$title_parameter)==0 ) { $file_name_return = $file; closedir($dir); return $file_name_return;}
		}
	}
	closedir($dir);
	$temporal_max ++;
	if(intval($file_number_counter) < $temporal_max) $file_number_counter = $temporal_max;

	return $file_name_return.$file_number_counter; // ±×·± Á¦¸ñÀ» °®´Â ÆäÀÌÁö°¡ ¾øÀ» °æ¿ì, "__NONEXISTANCE__ÀüÃ¼°Ë»öÇÑÆÄÀÏ°¹¼ö" ¸¦ ¸®ÅÏÇÑ´Ù.
}

// ### ÁÖ¾îÁø ½Ã°£À» °¡Áö°í, ±× ½Ã°£ ÀÌÈÄ·Î ÃÖ½Å ¾÷µ¥ÀÌÆ® ÆÄÀÏÀÇ ÆÄÀÏ¸íÀ» Ã£¾Æ µ¹·ÁÁÖ´Â ÇÔ¼ö #########################
function wkfunc_newest_file_find($time_parameter)
{
	$file_number_counter = 0;
	$file_name_return = "__NONEXISTANCE__";
	$temporal_file_pointer = 0;
	$temporal_title = "";
	$temporal_time_str = "";
	$temporal_time = "";
	$temporal_start = 0;
	$temporal_end = 0;
	$temporal_maximizer = 0;
	$temporal_return_value = "";

	if(file_exists("wkctlist.xml")!=false){
	$fp = fopen("wkctlist.xml", "r");
	$tmp_xml_contents = fread( $fp, filesize( "wkctlist.xml" ) );
	fclose( $fp );

		$times = explode( "</time>", $tmp_xml_contents );
		$titles = explode( "</title>", $tmp_xml_contents );
		for($l=0;$l<count($times);$l++){
			$I = strpos($times[$l],"<time>")+6;
			$temporal_time_str = substr($times[$l], $I);
			$I = strpos($titles[$l],"<title>")+7;
			$title = substr($titles[$l], $I);

			$temporal_start = strpos( $temporal_time_str, ": ")+2;
			$temporal_end = strpos( $temporal_time_str," /");
			$temporal_time = substr( $temporal_time_str , $temporal_start , $temporal_end - $temporal_start) + 0;

			
			if( $temporal_time > $temporal_maximizer && ($temporal_time < $time_parameter || $time_parameter==0 ) )
			{
			$temporal_maximizer=$temporal_time;
			$temporal_return_value=$temporal_time.":".urldecode($title)."#CQSW?=".$temporal_time_str;
			}
		}
	return $temporal_return_value;
	}

	$dir=opendir("./");
	while($file = readdir($dir)) {
	    if( strpos($file,"kct")==1 && strpos($file,".xml") <1 && (strpos($file,".txt") != false || strpos($file,".TXT") != false)  && strcmp(str_replace(".bak","",$file),$file)==0)
		{
		$file_number_counter++;
		$temporal_file_pointer = fopen( $file, "r" );
		$temporal_title = fgets($temporal_file_pointer);
		$temporal_time_str = fgets($temporal_file_pointer);
		$temporal_line1 =    fgets($temporal_file_pointer);
		$temporal_line2 =    fgets($temporal_file_pointer);
		fclose( $temporal_file_pointer );

		$temporal_title = str_replace( "\r", "" , $temporal_title );
		$temporal_title = str_replace( "\n", "" , $temporal_title );

		$temporal_start = strpos( $temporal_time_str, ": ")+2;
		$temporal_end = strpos( $temporal_time_str," /");
		$temporal_time = substr( $temporal_time_str , $temporal_start , $temporal_end - $temporal_start) + 0;

		if( $temporal_time > $temporal_maximizer && ($temporal_time < $time_parameter || $time_parameter==0 ) )
		{
		if(strcmp($file,"wklog.txt")==0) continue;
		$temporal_maximizer=$temporal_time;
		$temporal_return_value=$temporal_time.":".$temporal_title."#CQSW?=".$temporal_time_str;

		$GLOBALS["global_string_temp"] = $temporal_line1."\n".$temporal_line2;
		$GLOBALS["global_string_temp"] = str_replace("<", "&lt;", $GLOBALS["global_string_temp"]);
		$GLOBALS["global_string_temp"] = str_replace(">", "&gt;", $GLOBALS["global_string_temp"]);}
	
		}
	}
	closedir($dir);
	return $temporal_return_value;
}

// ### ÁÖ¾îÁø ¹®ÀÚ¿­À» °¡Áö°í, ±× ¹®ÀÚ¿­º¸´Ù »çÀü¼øÀ¸·Î ¹Ù·Î µÚÂÊ¿¡ ÀÖ´Â Á¦¸ñÀ» °¡Áø ÆÄÀÏ¸íÀ» Ã£¾Æ µ¹·ÁÁÖ´Â ÇÔ¼ö #####
function wkfunc_first_title_file_find($title_parameter)
{
	// $title_parameter = urlencode($title_parameter);

	$file_number_counter = 0;
	$file_name_return = "__NONEXISTANCE__";
	$temporal_file_pointer = 0;
	$temporal_title = "";
	$temporal_maximizer = "ÆRÆRÆR";
	$temporal_return_value = "";

	$title_parameter = str_replace( "\r", "" , $title_parameter );
	$title_parameter = str_replace( "\n", "" , $title_parameter );

	$dir=opendir("./");
	while($file = readdir($dir)) {
	    if( strpos($file,".xml") <1 && strpos($file,"kct")==1 && (strpos($file,".txt") != false || strpos($file,".TXT") != false)&& strpos($temporal_title,".bak")==false  && strcmp(str_replace(".bak","",$file),$file)==0 )
		{
		$file_number_counter++;
		$temporal_file_pointer = fopen( $file, "r" );
		$temporal_title = fgets($temporal_file_pointer);
		$temporal_time_str = fgets($temporal_file_pointer);
		fclose( $temporal_file_pointer );

		$temporal_title = str_replace( "\r", "" , $temporal_title );
		$temporal_title = str_replace( "\n", "" , $temporal_title );

		$temporal_title_o = $temporal_title."";
		// $temporal_title	  = urlencode( $temporal_title );
		
		if(strlen($temporal_title)<2) continue;
		if(strpos($temporal_title,"?xml ")==1) continue;
		//echo($file_number_counter.":".$file.":".$temporal_title.":".strpos($temporal_title,"?xml ")."\n");

		if(
		(strcmp($temporal_title, $temporal_maximizer)<0 || strcmp($temporal_maximizer,"ÆRÆRÆR")==0 ) &&
		(strcmp($temporal_title,$title_parameter)>0 || strlen($title_parameter)<1 ) 
		)

		{$temporal_maximizer=$temporal_title;
		$temporal_return_value=$temporal_title_o;}
		}
	}
	closedir($dir);
	return $temporal_return_value;
}

// ### Á¦¸ñ¼øÀ¸·Î Á¤·ÄµÈ Á¦¸ñ ¸ñ·ÏÀ» ¸¸µé¾î µ¹·ÁÁÖ´Â ÇÔ¼ö ############################################################
function wkfunc_make_title_sorted_list(){
	$file_number_counter = 0;
	$file_name_return = "__NONEXISTANCE__";
	$temporal_file_pointer = 0;
	$temporal_title = "";
	$temporal_return_value = "";
	$title_sorted_list = "";

	$title_parameter = str_replace( "\r", "" , $title_parameter );
	$title_parameter = str_replace( "\n", "" , $title_parameter );

	$dir=opendir("./");

	while($file = readdir($dir)) {
	    if(
		strpos($file,".xml") <1 
	&& 	strpos($file,"kct")==1 
	&& 	(strpos($file,".txt") != false || strpos($file,".TXT") != false)
	&&	strpos($temporal_title,".bak")==false  
	&&	strcmp(str_replace(".bak","",$file),$file)==0 
	&&	strpos($file,".xml")<1 
		)

		{
		$file_number_counter++;
		$temporal_file_pointer = fopen( $file, "r" );
		$temporal_title = fgets($temporal_file_pointer);
		$temporal_time_str = fgets($temporal_file_pointer);
		fclose( $temporal_file_pointer );

		$temporal_title    = str_replace( "\n","",$temporal_title);
		$temporal_title    = str_replace( "\r","",$temporal_title);
		if(strcmp($temporal_title,"»èÁ¦")==0) continue;
		$temporal_time_str = str_replace( "\n","",$temporal_time_str);
		$temporal_time_str = str_replace( "\r","",$temporal_time_str);
		$temporal_time_str = substr($temporal_time_str, strpos($temporal_time_str," 20",strpos($temporal_time_str,"UNIX") ) );

		if(strlen($temporal_title)>1 && strlen($temporal_time_str)>1){
			$needle_point = wkfunc_find_sorted_position_from_list($temporal_title,$title_sorted_list);
			$title_sorted_list = substr($title_sorted_list, 0, $needle_point )." *   [ ".$temporal_title." ]  ".$temporal_time_str." \n".substr($title_sorted_list, $needle_point);
			}
		}
	}
	closedir($dir);

	$title_sorted_list = $title_sorted_list."Total ".$file_number_counter." pages.\n";

	return $title_sorted_list;
}

// ### stack ¹®ÀÚ¿­¿¡¼­ needle ¹®ÀÚ¿­ÀÌ »çÀü¼øÀ¸·Î ¹è¿­ÇÏ¸é ¾îµð¿¡ ³¢¿©¾ß ÇÏ´ÂÁö ¹®ÀÚ¿­ ÀÎµ¦½º¸¦ Ã£¾ÆÁÖ´Â ÇÔ¼ö #######
function wkfunc_find_sorted_position_from_list($needle, $stack){
	$I = 0;
	$i = 0;
	$ret = 0;
	for($i=0;$i<5000;$i++){
		if( strpos( $stack,  "*   [", $I)<1 ) break;
		$I = strpos( $stack, "*   [", $I)+5;
		$E = strpos( $stack, "]", $I);
		$E2= strpos( $stack, "\n",$E)+1;

		$temp_title = substr($stack, $I, $E-$I);
		$temp_title = str_replace(" ","",$temp_title);
		if(strcmp($temp_title,$needle)<0) $ret=$E2;
		$I = $E;
	}

	return $ret;
}
// ### ÇÔ¼ö ±¸ÇöºÎ ³¡ ################################################################################################


// ### ½ÇÁ¦ ½ÇÇàºÎºÐ #################################################################################################
$fp=0;
$i=0;
$j=0;
$k=0;
$l=0;

$page_to_read="";
$page_to_edit="";
$filename_to_edit="";
$filename_to_read="";

$wk_design="";
$wk_title="";
$wk_date="";
$wk_contents="";
$wk_password="";
$wk_contents_ori="";
$wk_first_page_title="";

$main_temp="";
$main_temp2="";

$wk_head="";
$wk_tail="";
$wk_output="";

$position_of_contents=0;
$position_of_blit=0;
$position_of_headline=0;

$deco_headline_start="";
$deco_headline_end="";
$deco_blit_start="";

$deco_url="";


// ### ¾Æ¹« µ¥ÀÌÅÍ ÆÄÀÏÀÌ ¾øÀ¸¸é µðÀÚÀÎ ÆÄÀÏºÎÅÍ ÀÚµ¿»ý¼º ÇÑ´Ù. ######################################################
$m_first_run_of_wiki=0;
if(file_exists("wk_design.htm")==false){
$m_first_run_of_wiki=1;
$fp = fopen( "wk_design.htm", "wb" );
$temp_data = "";
$temp_data = $temp_data."<!--NOOEKAKI-->";
$temp_data = $temp_data."<title>WikiKiWiTitle</title>\n";
$temp_data = $temp_data."<H1>WikiKiwiTitle</H1>\n";
$temp_data = $temp_data."<H5>Last modified at LastEditedTime</H5>\n";
$temp_data = $temp_data."<table width=750><tr><td>\n";
$temp_data = $temp_data."\n";
$temp_data = $temp_data."WikiPageContent<BR><BR><H3>HeadlineSubtitle</H3><BR><BR>\n<B>-</B>ListingBlit<BR>\n";
$temp_data = $temp_data."\n";
$temp_data = $temp_data."</td></tr></table><BR/>\n";
$temp_data = $temp_data."<a href='EditPageLink'>Edit</a>\n";
$temp_data = $temp_data."<a href='ReplyPageLink'>Reply</a>\n";
$temp_data = $temp_data."<a href='UpdatePageLink'>Update</a>\n";
$temp_data = $temp_data."<a href='ListPageLink'>List</a>\n";
$temp_data = $temp_data."<a href='index.xml'>XML</a>\n";
fwrite( $fp, $temp_data );
fclose( $fp );}
if(file_exists("wkct0.txt")==false){
$fp = fopen( "wkct0.txt", "wb" );
$temp_data = "";
$temp_data = $temp_data."Start\n";
$temp_data = $temp_data."UNIX clock : 426426 / Common clock 0426.04.26, 4:26 pm\n";
$temp_data = $temp_data."Wecome to WikiKiwi.\n";
$temp_data = $temp_data."If you want to know anything about Wikikiwi, then please visit [http://no-smok.net/nsmk/WikiKiwi] .";
$temp_data = $temp_data."";
fwrite( $fp, $temp_data );
fclose( $fp );}



// ### µðÀÚÀÎ ÆÄÀÏÀ» ÀÐ¾î µéÀÎ´Ù. ####################################################################################
$fp = fopen( "wk_design.htm", "r" );
$wk_design = fread( $fp, filesize( "wk_design.htm" ) );
fclose( $fp );

if(strpos($HTTP_GET_VARS["option"],"tatic")==1){ // static ¿É¼ÇÀÌ ÀÖÀ» °æ¿ì, ´Ù¸¥ µðÀÚÀÎ ÆÄÀÏÀ» ÀÐ´Â´Ù.
if(file_exists("wk_design_static.htm")!=false){
$fp = fopen( "wk_design_static.htm", "r" );
$wk_design = fread( $fp, filesize( "wk_design_static.htm" ) );
fclose( $fp );
}}

// ### µðÀÚÀÎ ÆÄÀÏÀ» ÇØ¼®ÇÑ´Ù. #######################################################################################
	// ´ë¹® ÆäÀÌÁö(¸ÞÀÎ ÆäÀÌÁö)ÀÇ Á¦¸ñÀ» ÀÐ¾î µÐ´Ù.
	$fp = fopen( "wkct0.txt", "r" );
	$wk_first_page_title = fgets($fp);
	fclose( $fp );

// ### ÆäÀÌÁö Áö¿ì±â. »èÁ¦ Ã³¸® ############################################################################
if( strlen($HTTP_GET_VARS["pagetoremove"])>0){
	$m_remove_title = $HTTP_GET_VARS["pagetoremove"];
	$m_remove_file =  wkfunc_file_find_from_title($m_remove_title);
	echo "deletion of ".$m_remove_title."<BR/>\n";
	echo "deletion of ".$m_remove_file."<BR/>\n";

	if(strpos($m_remove_file, "NONEXISTANCE__")>0) {echo "nofile";exit(1);}

	if(file_exists("wkctlist.xml")!=false){
	$fp = fopen( "wkctlist.xml", "rb" );
        $xmllist = fread( $fp, filesize( "wkctlist.xml" ) );
	fclose( $fp );

	$xmllist = str_replace( "\r\n", "\n", $xmllist);
	$xmllist = str_replace( "<?xml version=\"1.0\" encoding=\"".$m_global_encoding."\"?>\n<xml>", "", $xmllist );
	$xmllist = str_replace( "</xml>", "", $xmllist );

	$I = strpos($xmllist, "<title>".$m_remove_title."<");
	$E = strpos($xmllist, "</file>", $I)+7;
	$m_remove_cont = substr($xmllist, $I, $E-$I);
		echo "".$I.":".$E."<BR/>\n";
		echo "<input type=text value='".$m_remove_cont."'/><BR/>";
	$I = strpos($xmllist, "<title>".urlencode($m_remove_title)."<");
	$E = strpos($xmllist, "</file>", $I)+7;
	$m_remove_cont_encoded = substr($xmllist, $I, $E-$I);
		echo "".$I.":".$E."<BR/>\n";
		echo "<input type=text value='".$m_remove_cont_encoded."'/><BR/>";

	$xmllist = str_replace( $m_remove_cont, 	"", $xmllist );
	$xmllist = str_replace( $m_remove_cont_encoded, "", $xmllist );

	$fpxml = fopen( "wkctlist.xml", "wb");
	$xmltags = "<?xml version=\"1.0\" encoding=\"".$m_global_encoding."\"?>\n<xml>".$xmllist."</xml>";
	fwrite($fpxml,$xmltags,strlen($xmltags) );
	fclose($fpxml);

	$fpxml = fopen( $m_remove_file, "wb");	fwrite($fpxml,".",1 );	fclose($fpxml);
	}	

	exit(1);
}
// ### ÆäÀÌÁö Áö¿ì±â Ã³¸® ³¡ #######


	// Áö±Ý ÀÐÀ» ÆäÀÌÁöÀÇ Á¦¸ñÀ» ÀÐ¾î µÐ´Ù.
if( strlen($page_to_read) < 1) {
	$page_to_read = $HTTP_GET_VARS["pagetoread"];
	
	// ÀÎÅÍÀ§Å°¸¦ À§ÇÑ Ä¡È¯ Ã³¸®
	$m_Meatball[0] = "Nsmk";
	$m_Prefix  [0] = "http://no-smok.net/nsmk/";
	$m_Meatball[1] = "Doosan";
	$m_Prefix  [1] = "http://kr.encycl.yahoo.com/enc/info.html?key=";
	$m_Meatball[2] = "Freefell";
	$m_Prefix  [2] = "http://freefeel.org/wiki/";
	$m_Meatball[3] = "KoWikiPedia";
	$m_Prefix  [3] = "http://wiki.kldp.org/wiki.php?action=goto&oe=utf-8&url=http://ko.wikipedia.org/wiki/";

	// ÀÎÅÍÀ§Å° ÆäÀÌÁö¸¦ ¿ä±¸ÇÏ¸ç ÆäÀÌÁö¸¦ ¿äÃ»ÇÒ °æ¿ì ÀÚµ¿À¸·Î ¸®´ÙÀÌ·º¼ÇÇÏ¸ç Á¾·á
	for($ii=0;$ii<4;$ii++){
	if( strpos(" ".$page_to_read, $m_Meatball[$ii].":" )==1 ) {
		$m_addr = substr($page_to_read, strlen($m_Meatball[$ii])+1, 180 );
		echo('<script language="javascript">location.replace("'.$m_Prefix[$ii].$m_addr.'")</script>');
		exit(1);
	}
	}
}

if( strlen($page_to_read) < 1 || strcmp($page_to_read,"FIRSTPAGE")==0) $wk_title = $wk_first_page_title;
else if( strcmp($page_to_read,"TITLELIST")==0) {$wk_title = "Title List";}
else if( strcmp($page_to_read,"SRESULT")==0) {$wk_title = "Search Result";}
else if( strcmp($page_to_read,"SEARCH")==0) {$wk_title = "Search";}
else if( strcmp($page_to_read,"UPDATELIST")==0) {$wk_title = "Updated Pages";}
else if( strcmp($page_to_read,"UPLOADED")==0) {$wk_title = "Upload Completed";}
else if( strcmp($page_to_read,"UPLOAD")==0) {$wk_title = "File Upload";}

else $wk_title = $page_to_read;

	if(strpos($page_to_read,"kct")>0 && strpos($page_to_read,".txt")>0 ) {
		$page_to_read = wkfunc_file_find_from_title($page_to_read);
		$wk_title = $page_to_read;
	}

	// µðÀÚÀÎ ÆÄÀÏ¿¡ ´ëÇÑ ´Ü¼ø °¡°ø
	$wk_design = str_replace("¸ÞÀÎÆäÀÌÁöÁ¦¸ñ",$wk_first_page_title, $wk_design);
	$wk_design = str_replace("Ã³À½ÆäÀÌÁöÁ¦¸ñ",$wk_first_page_title, $wk_design);
	$wk_design = str_replace("À§Å°Å°À§Á¦¸ñ",str_replace("_"," ",$wk_title), $wk_design);
	$wk_design = str_replace("ÆäÀÌÁö°íÄ¡±â¸µÅ©","index.php?pagetoedit=".$wk_title."&option=oekaki",$wk_design);
	$wk_design = str_replace("¿ª¸µÅ©ÆäÀÌÁö¸µÅ©","index.php?pagetoread=SRESULT&sphrase=[ ".$wk_title." ]",$wk_design);
	$wk_design = str_replace("ÆäÀÌÁö°íÄ¡±â","index.php?pagetoedit=".$wk_title."&option=oekaki",$wk_design);
	$wk_design = str_replace("ÅØ½ºÆ®°íÄ¡±â¸µÅ©","index.php?pagetoedit=".$wk_title,$wk_design);
	$wk_design = str_replace("Æ®·¢¹éº¸³»±â¸µÅ©","index.php?pagetoread=".$wk_title."&option=trackback",$wk_design);	
	$wk_design = str_replace("´Þ¶óÁøÁ¡º¸±â¸µÅ©","index.php?pagetoread=".$wk_title."&option=diff",$wk_design);	
	$wk_design = str_replace("¿¾³¯ÆÇº¸±â¸µÅ©","index.php?pagetoread=".$wk_title."&option=history",$wk_design);	
	$wk_design = str_replace("Á¤ÀûHTML»ý¼º¸µÅ©","index.php?pagetoread=".$wk_title."&option=static",$wk_design);	
	$wk_design = str_replace("Ã³À½ÆäÀÌÁö¸µÅ©","index.php?pagetoread=FIRSTPAGE",$wk_design);
	$wk_design = str_replace("¸ñ·ÏÆäÀÌÁö¸µÅ©","index.php?pagetoread=TITLELIST",$wk_design);
	$wk_design = str_replace("¾÷µ¥ÀÌÆ®ÆäÀÌÁö¸µÅ©","index.php?pagetoread=UPDATELIST",$wk_design);
	$wk_design = str_replace("ÆÄÀÏ¾÷·Îµå¸µÅ©","index.php?pagetoread=UPLOAD",$wk_design);
	$wk_design = str_replace("°Ë»öÆäÀÌÁö¸µÅ©","index.php?pagetoread=SEARCH",$wk_design);
	$wk_design = str_replace("µµ¿ò¸»ÆäÀÌÁö¸µÅ©","index.php?pagetoread=À§Å°Å°À§%20µµ¿ò¸»",$wk_design);
	$wk_design = str_replace("»õ·Î¿îÆäÀÌÁö¸µÅ©","index.php?pagetoedit=Á¦¸ñÀÔ·Â&option=oekaki", $wk_design);
	$wk_design = str_replace("À§Å°Å°À§RSS¸µÅ©","index.xml",$wk_design);

	$wk_design = str_replace("FirstPageTitle",$wk_first_page_title, $wk_design);
	$wk_design = str_replace("WikiKiwiTitle",str_replace("_"," ",$wk_title), $wk_design);
	$wk_design = str_replace("WikiKiwiRSSLink","index.xml",$wk_design);
	$wk_design = str_replace("SearchPageLink","index.php?pagetoread=SEARCH",$wk_design);
	$wk_design = str_replace("ListPageLink","index.php?pagetoread=TITLELIST",$wk_design);
	$wk_design = str_replace("UpdatePageLink","index.php?pagetoread=UPDATELIST",$wk_design);
	$wk_design = str_replace("EditPageLink","index.php?pagetoedit=".$wk_title."&option=oekaki",$wk_design);


// ### µðÀÚÀÎ ÆÄÀÏ¿¡ ±â·ÏµÈ ¿ä¼ÒÀÇ parsing ###########################################################################
$position_of_contents = strpos($wk_design,"ÆäÀÌÁö³»¿ë");
$position_of_headline = strpos($wk_design,"Çìµå¶óÀÎ¼ÒÁ¦¸ñ");
$position_of_blit = strpos($wk_design,"³ª¿­ºí¸´");
$headline_length = strlen("Çìµå¶óÀÎ¼ÒÁ¦¸ñ");
if($position_of_contents<1) $position_of_contents = strpos($wk_design,"WikiPageContent");
if($position_of_headline<1) {$position_of_headline = strpos($wk_design,"HeadlineSubtitle");$headline_length = strlen("HeadlineSubtitle");}
if($position_of_blit<1) $position_of_blit = strpos($wk_design,"ListingBlit");

	// Get the style of each design element
		// find the position of '<BR>' just in front of blit : $j
$j=0; // $j is the postion of the '<BR>' meaning starting of blit
for($i=0;$i<$position_of_blit;$i++){
	$k = strpos($wk_design,"<BR>",$i);
	if($k<$position_of_blit) $j=$k;
}
		// find the postion of '<BR>' just after contents : $k
$k = strpos($wk_design,"<BR>",$position_of_contents);

$deco_blit_start = substr( $wk_design, $j+4, $position_of_blit - $j - 4 );
$deco_headline_start = substr( $wk_design, $k+4, $position_of_headline - $k -4);
$deco_headline_end = substr( $wk_design, $position_of_headline + $headline_length, $j - $position_of_headline - $headline_length );
$end_of_position_of_blit = strpos($wk_design,"\n",$position_of_blit);

	// Eliminate design elements from design file
$wk_head = substr( $wk_design, 0, $position_of_contents);
$wk_tail = substr( $wk_design, $end_of_position_of_blit+1);



// ### ÆäÀÌÁö ¼öÁ¤ ¿äÃ»ÀÏ °æ¿ì #######################################################################################
$page_to_edit = $HTTP_GET_VARS["pagetoedit"];
$j = $HTTP_GET_VARS["edittype"];

if( strcmp($page_to_edit,"FIRSTPAGE") == 0 ) { // Ã¹ ÆäÀÌÁöÀÏ °æ¿ì º¼ °Íµµ ¾øÀÌ wkct0.txt ÆÄÀÏ¿¡ ´ëÇÑ ÆíÁýÀÓÀº È®½ÇÇÏ´Ù.
	$filename_to_edit = "wkct0.txt";
	$createnew = 0;
} else
if( strlen($page_to_edit) >= 1 ) {

$filename_to_edit = wkfunc_file_find_from_title($page_to_edit);
$createnew = 0;

if(strpos($HTTP_GET_VARS["pagetoedit"],"llYourBaseAreBelongToUs.Php")>0) { // Direct executive php editing: hidden function, disable default.
		$createnew = -1;
   		$filename_to_edit="allyourbasearebelongtous.php";
		
   		// if you want to use live executive editting, then remove the 1 line below. 
   		exit(1);
} else
if(strpos($filename_to_edit,"_NONEXI")>0) {$createnew=1; $filename_to_edit="wkct".substr($filename_to_edit,16).".txt";} // °íÄ¡°íÀÚÇÏ´Â Á¦¸ñÀÇ ÆäÀÌÁö°¡ ¾ø´Â °æ¿ì, »õ·Î¿î ÀÌ¸§À¸·Î ÆÄÀÏ »ý¼º

// ### ¼öÁ¤À» À§ÇØ ¿ø·¡ÀÇ ³»¿ëÀ» ÀÐ´Â´Ù. #############################################################################
if($createnew<=0){
	$fp = fopen( $filename_to_edit, "r" );
if($createnew==0) $wk_title =fgets($fp); else $wk_title = " ";
if($createnew==0) $wk_date  =fgets($fp); else $wk_date =  " ";
$wk_contents = fread( $fp, filesize( $filename_to_edit ) - strlen($wk_title) - strlen($wk_date));
if($createnew==-1)$wk_contents=str_replace("<","&lt;",$wk_contents);
fclose( $fp );
} else {
	$wk_title = $HTTP_GET_VARS["pagetiedit"];
	$wk_date = date("s");
	$wk_contents = "";
}
$wk_contents = str_replace("\r","",$wk_contents);


	// ¼öÁ¤ Á¦ÇÑ ¾ÏÈ£(È¤Àº ´ä±Û¸¸ Çã¿ë)°¡ °É·Á ÀÖ´Â °æ¿ìÀÇ Ã³¸®
$i = substr($wk_contents,0,5);
if((strcmp($i,"¾ÏÈ£ ")==0 || strcmp($i,"¾Õ±Û ")==0  || strcmp($i,"´ä±Û ")==0)  && strcmp($j,"reply") != 0)
{
	$i = strpos($wk_contents,"\n");
	$wk_password = substr($wk_contents,5,$i-5);
	if( strcmp($HTTP_GET_VARS["pwd"],$wk_password)!=0) { // ¾ÏÈ£¸¦ Á¦°ø ¹ÞÁö ¸øÇß°Å³ª, ¾ÏÈ£°¡ ´Ù¸¥ °æ¿ì, ¾ÏÈ£¸¦ ¹°¾îº¸´Â Ã¢À» ¶ç¿ò
	echo "<script language='javascript'>\n";
	echo "var pwd;\n";
	echo "pwd = prompt('Password');\n";
	echo "location.replace('index.php?pagetoedit=$page_to_edit&pwd='+pwd);\n";
	// ¾ÏÈ£´Â get ¹æ½ÄÀ¸·Î ´Ù½Ã Àü´ÞµÊ.
	echo "</script>\n";
	exit();
	} // ¾ÏÈ£°¡ Á¦°øµÇ¾úÀ¸¸ç, ÀÏÄ¡ÇÏ´Â °æ¿ì ÀÏ¹ÝÀûÀÎ ¼öÁ¤ ÀÛ¾÷À¸·Î ÀÌ¾îÁü.
}

echo $wk_head;
echo "<form method=post action=index.php>\n";
$redirection_home_url = $HTTP_GET_VARS["homeurl"];
if( ( strcmp($i,"¾Õ±Û ")==0  && strcmp($j,"eply") == 1) || ( strcmp($i,"´ä±Û ")==0  && strcmp($j,"eply") == 1) || strcmp($j,"eply") == 1 )
{ // µ¡±Û ·ùÀÏ °æ¿ì, ¶ç¿ì´Â formÀÇ ÇüÅÂ°¡ ´Ù¸§
echo "<H3>Reply to \"<B>".$page_to_edit."</B>\"</H3><BR/>\n";
echo "Name <input name=name><BR>\n";
//echo "Anti-spam Question: Just type 6 alphabet characters, \"hehehe\" <input name=nospam><BR>\n";
echo "Content <BR><TEXTAREA name=contents rows=5 cols=90>\n";
echo $HTTP_GET_VARS["defaulttext"];
echo "</TEXTAREA><BR>\n";
echo "Homepage URL <input name=hompurl><BR>\n";
if($m_static_flag==1 && $m_static_auto_replace==0) $redirection_home_url="index.php?pagetoread=".urlencode($page_to_edit)."&redirect=static"; // STATIC_BASED_PAGE
}
else
{ // ÀÌ°ÍÀÌ ÀÏ¹ÝÀûÀÎ form
echo  "Title <input name=title value=\"$page_to_edit\"><BR>\n";
echo "<TEXTAREA name=contents rows=30 style='width:100%; line-height:150%;'>\n";
echo $wk_contents.$HTTP_GET_VARS["defaulttext"];
echo "</TEXTAREA><BR>\n";
}
echo "<input type=hidden name=homeurl value=\"".$redirection_home_url."\">\n";
echo "<input type=hidden name=pagetowrite value=\"$filename_to_edit\">\n";
echo "<input type=hidden name=itstitle value=\"$page_to_edit\">\n";
echo "<input type=hidden name=edittype value=\"$j\">\n";
echo "<input type=submit value=\"Submit\">\n";
echo "<BR>";

if( ( strcmp($i,"¾Õ±Û ")==0  && strcmp($j,"eply") == 1) || ( strcmp($i,"´ä±Û ")==0  && strcmp($j,"eply") == 1) || strcmp($j,"eply") == 1 )
	{}
	else {
	echo "<select name='filelinklist'>\n"; // edit ½Ã »ç¿ëµÇ´Â ÆÄÀÏ¸µÅ© ¸ñ·ÏÀ» À§ÇØ

	$dir=opendir("./");
	while($file = readdir($dir)) {
        	if(
	        (strpos($file,"kct") && strpos($file,".txt")) ||
        	(strpos($file,"kct") && strpos($file,".bak")) ||
        	$file == "." ||
        	$file == ".." ||
        	(strpos($file,".php")) ||
        	(strpos($file,".htm"))
        	)
        	if(strpos(":".$file,$filename_to_edit)==1 && strpos($file,".bak")>4)
			echo "<option>".$file."</option>\n"; else ;
        	else echo "<option>".$file."</option>\n";
	}
	closedir($dir);

	echo "</select>\n";

	echo "<input type=button value='Paste file link' OnClick='fileattach()'>\n";
	echo "<input type=checkbox name=autolinkgen value=false>Automatic linking\n";
	}

echo "</form>\n";
echo "Emoticon  :)  B)  ;)  :D  X-(  :o  :(  --;  ^^  ^__^   {!}  {V}  {ok}  {x}  {i}<BR>\n";
echo "<script language='javascript'>\n";
echo "function fileattach()\n";
echo "{\n";
echo "         filename = ' [LINK:' +document.forms[document.forms.length-1].filelinklist[ document.forms[document.forms.length-1].filelinklist.selectedIndex ].text + '] ';\n";
echo "         document.forms[document.forms.length-1].contents.value += filename;\n";
echo "}\n";
echo "</script>\n";

// ### ¿À¿¡Ä«Å°¸¦ À§ÇÑ ÀÚ¹Ù½ºÅ©¸³Æ®¸¦ µ¡ºÙÀÎ´Ù. ######################################################################
// ### ÀüÇô »ç¿ëÇÏÁö ¾Ê°Å³ª ´ëÃ¼ÇÒ °æ¿ì ÀÌ°÷À» ÆíÁý. #################################################################
if(strpos($HTTP_GET_VARS["option"],"ekaki")==1 && strpos($wk_design,"!--NOOEKAKI--")<=0 )
 echo '
<div style="line-height: 0pt ; font-size : 20 ;">
<script language="javascript">
var width=40;
var formno=0;
var image_no_offset=-1;
formno = document.forms.length - 1;

var toggle=0;
var data = new Array(width*width);
function attach_standalone(){
	var offset = image_no_offset;
        var content = "<table cellspacing=0 cellpadding=0>\n";

        for(i=0;i<width;i++){
        content += "<tr height=5>";
                for(j=0;j<width;j++){
                content += ("<td bgcolor="+data[width*i+j+offset]+" width=5 height=5></td>");
                }
        content +="</tr>";
        }
        content +="\n</table>";
        document.forms[formno].contents.value=content;
}

function attach_wikikiwi(){
	var offset = image_no_offset;
        var content = "@JSoekaki_start@\n";

        for(i=0;i<width;i++){
        content += "{";
                for(j=0;j<width;j++){
                _src = document.images[width*i+j+offset].src;
                content += ( _src.charAt(_src.indexOf(".gif")-2) );
                }
        content +="}";
        }
        content +="\n@JSoekaki_end@";
        document.forms[formno].contents.value+=content;
}

function clicked(x,y)
{
	if(image_no_offset==-1){
		var L=document.images.length;
		for(i=0;i<L;i++){
		if(document.images[i].src.indexOf("bb.gif")>=0 && document.images[i].width==5)
			{image_no_offset = i;break;}
		}
	}
	var offset = image_no_offset;
        var I = width*y+x+offset;

        if(toggle==1) {
                document.images[I].src = colorsrc;
                data[I] = colordata;
        }
}

var ex=-1, ey=-1;

function togglepaint(x,y){
        if(toggle==0) {
		toggle=1;clicked(x,y);
		if(ex==x && ey==y) toggle=0;
	} else if(toggle==1) toggle=0;
	if(toggle==2) paintIt(x,y);
	ex=x;ey=y;
}

var colorcode = 0;
var colorsrc = "bb.gif";
var colordata = "#000000";

function pallette(){
 var offset = image_no_offset; colorcode++;
 colorsrc=numberToColor(colorcode);
 document.images[width*width+offset].src=(colorsrc);
 if(toggle!=2) toggle=0;
}

function numberToColor(N){
        if(N%8==0) {colordata="#000000";return "bb.gif";}
        if(N%8==1) {colordata="#FFFFFF";return "wb.gif";}
        if(N%8==2) {colordata="#FF0000";return "rb.gif";}
        if(N%8==3) {colordata="#FFFF00";return "yb.gif";}
        if(N%8==4) {colordata="#00FFFF";return "ob.gif";}
        if(N%8==5) {colordata="#0000FF";return "sb.gif";}
        if(N%8==6) {colordata="#00FF00";return "gb.gif";}
        if(N%8==7) {colordata="#5B5B5B";return "db.gif";}
}

function getSrcColor(x, y){
	var offset = image_no_offset;
        var I = width*y+x+offset;
        return( document.images[I].src );
}

function setSrcColor(x, y, c){
	var offset = image_no_offset;
        var I = width*y+x+offset;
        data[I] = colordata;
	document.images[I].src = colorsrc;
}

function paintIt(x, y){
	var color = getSrcColor(x,y);
	var ys = y, ye = y;
        for(i=y;i>=0;i--) {
		if(getSrcColor(x,i)!=color) break;
		setSrcColor(x,i);
		ys=i;
	}
        for(i=y+1;i<width;i++) {
		if(getSrcColor(x,i)!=color) break;
		setSrcColor(x,i);
		ye=i;
	}
	for(j=ys;j<=ye;j++){
	for(i=x-1;i>=0;i--){
		if(getSrcColor(i,j)!=color) break;
		setSrcColor(i,j);
	}
	for(i=x+1;i<width;i++){
		if(getSrcColor(i,j)!=color) break;
		setSrcColor(i,j);
	}}
}

</script>



<script language="javascript">
for(i=0;i<width;i++){
for(j=0;j<width;j++) {
        var I = width*i+j;
        data[I] = "#FFFFFF";
        src = "wb.gif";
        if(i==0 || j==0 || i==width-1 || j==width-1) src="bb.gif";
        document.write("<img src=\""+src+"\" onmouseover=\"clicked("+j+","+i+")\" width=5 height=5 margin=0 hspace=0 vspace=0 border=0 onclick=\"togglepaint("+j+","+i+")\">");
}
document.writeln("<BR>");
}

</script>
<BR>
<img src="bb.gif" width=32 height=32 onclick="pallette()" border=1>
<img src="icoatta.gif" onClick="attach_wikikiwi()"><img src="draw.gif" onClick="toggle=0"><img src="paint.gif" onClick="toggle=2">
</div>

<!-- if you want to use this in your zboard or other web application then add comment marking to the below -->
<!--
<HR/>
<form>
<textarea name=contents cols=40 rows=20>




</textarea>
</form>
-->
';
// ### ¿À¿¡Ä«Å° °ü·Ã ºÎºÐ Ã³¸® ¿Ï·á ##################################################################################

echo $wk_tail;

exit();
}
// ### ÆäÀÌÁö ¼öÁ¤¿¡ °üÇÑ ³»¿ë Ã³¸® ¿Ï·á #############################################################################


// ### ÆäÀÌÁö ³»¿ëÀ» ½ÇÁ¦·Î ÆÄÀÏ¿¡ ¾²´Â ºÎºÐ #########################################################################
// ### ÆäÀÌÁö ÆíÁýÀ» ÇÑ ÈÄ submit °á°ú·Î ¿äÃ»ÇÏ°Ô µÇ´Â ÆäÀÌÁö´Â ¹Ù·Î ÀÌ ºÎºÐÀÌ µÈ´Ù. #################################
// ### Æ®·¢¹éµµ ÀÌ°÷¿¡¼­ Ã³¸®ÇÔ. #####################################################################################
$page_to_read = "";
$page_to_edit = "";
$page_to_edit = $HTTP_POST_VARS["pagetowrite"];
if(strlen($page_to_edit)<1 && strlen($HTTP_POST_VARS["blog_name"])<2) $page_to_edit = $HTTP_GET_VARS["pagetowrite"];

// ### Æ®·¢¹éÃ³¸® ###################################################################################################
if(strlen($page_to_edit)<1) { // blog_nameÀÌ ¼³Á¤µÈ, POST°¡ ¾Æ´Ñ get¹æ½ÄÀ¸·Î¸¸ ÆÄÀÏÀÌ¸§À» Á¤ÇÏ´Â ¿äÃ»Àº Æ®·¢¹éÃ³¸®ÀÓ
	$page_to_edit = $HTTP_GET_VARS["pagetowrite"];
	if(strpos($HTTP_POST_VARS["url"],"://")>0){
	$HTTP_POST_VARS["pagetowrite"] = $HTTP_GET_VARS["pagetowrite"];
	$HTTP_POST_VARS["edittype"] = "reply";
	$tb_title = $HTTP_POST_VARS["title"];
        $fp = fopen( $HTTP_POST_VARS["pagetowrite"], "r" );
        $wiki_title=fgets($fp);
	fclose($fp);

	if(strlen($wiki_title)<2) exit(1);

	$wiki_title = str_replace("\r","",$wiki_title);
	$wiki_title = str_replace("\n","",$wiki_title);
	$HTTP_POST_VARS["title"] = $wiki_title;
	$HTTP_POST_VARS["nospam"] = "hehehe";

	$HTTP_POST_VARS["name"] = $HTTP_POST_VARS["blog_name"];
$HTTP_POST_VARS["excerpt"]=str_replace("'","",$HTTP_POST_VARS["excerpt"]);
$HTTP_POST_VARS["excerpt"]=str_replace("`","",$HTTP_POST_VARS["excerpt"]);
$HTTP_POST_VARS["excerpt"]=str_replace("=","",$HTTP_POST_VARS["excerpt"]);
$HTTP_POST_VARS["excerpt"]=str_replace("[","",$HTTP_POST_VARS["excerpt"]);
$HTTP_POST_VARS["exceprt"]=str_replace("]","",$HTTP_POST_VARS["excerpt"]);
	$HTTP_POST_VARS["contents"] = " ''' ".$tb_title."  [".$HTTP_POST_VARS["url"]."] ''' ".$HTTP_POST_VARS["excerpt"];
	} // Æ®·¢¹éÀº ÀÏÁ¾ÀÇ ´ä±Û·Î Ã³¸®µÈ´Ù. Áï, Æ®·¢¹éÀ» º¸³»±â À§ÇØ ³¯¾Æ¿Â ³»¿ëµéÀº ´ä±ÛÀ» À§ÇØ Àü´ÞµÈ Á¤º¸ÀÎ °Í Ã³·³ º¯È¯µÇ°í, ÀÏ¹Ý ´ä±ÛÃ³¸®·Î ÀÌ¾îÁö°Ô µÈ´Ù.
}

// ### ÀÏ¹Ý ÆÄÀÏ ¾²±â Ã³¸® ###########################################################################################
if( strlen($page_to_edit) >= 1 ) {
$reply_edit_type = $HTTP_POST_VARS["edittype"];
if(strlen($reply_edit_type)<1) $reply_edit_type = $HTTP_GET_VARS["edittype"];
if(strcmp($reply_edit_type,"reply")==0) // ¸¸¾à ´ä±ÛÀÌ¶ó¸é,
{
	if(file_exists($page_to_edit)) ; else exit(1);

	$fp = fopen( $page_to_edit, "r" );
	$wk_title = fgets($fp);
	$wk_date  = fgets($fp);
	$wk_contents_ori = fread( $fp, filesize( $page_to_edit ) - strlen($wk_title) - strlen($wk_date));
	$wk_contents_ori = str_replace("\r","",$wk_contents_ori);
	fclose( $fp );

	// ¾ÏÈ£¸¦ ±âÀÔÇÑ´Ù.
	$i = substr($wk_contents_ori,0,5);
	if( strcmp($i,"¾ÏÈ£ ")==0 || strcmp($i,"¾Õ±Û ")==0  || strcmp($i,"´ä±Û ")==0 ) {
	$k = strpos($wk_contents_ori,"\n");
	$j = substr($wk_contents_ori,0,$k); // $j is the first line of the file
	$i = substr($wk_contents_ori,0,5); // $i is the type of protection
	$wk_contents_ori = substr($wk_contents_ori,$k+1); // $wk_contents_ori is pure contents
	}
}

$temp_url_title = $HTTP_POST_VARS["title"];    if(strlen($temp_url_title)<1) $temp_url_title = $HTTP_GET_VARS["title"];
$temp_url_contt = $HTTP_POST_VARS["contents"]; if(strlen($temp_url_contt)<1) $temp_url_contt = $HTTP_GET_VARS["contents"];

	if(strlen($temp_url_title)<1) $wk_title = $HTTP_POST_VARS["itstitle"]; else $wk_title = "".$temp_url_title;
	$wk_contents = "".$temp_url_contt;
        if( strlen($HTTP_POST_VARS["autolinkgen"]) > 0 ){ // Autolinkgeneration
            $wk_date ="";
            $main_temp2 = "";
            $i = 0;

            for($l=0;$l<300;$l++){
            	$main_temp = wkfunc_first_title_file_find($main_temp2);
             	if(strcmp($main_temp,$main_temp2)==0 || strlen($main_temp)<2 ) break;
              	if(strcmp($main_temp,"»èÁ¦")!=0 && strcmp($main_temp,$wk_title)!=0) {
                 $wk_contents = str_replace( " [ ".$main_temp." ] ", $main_temp, $wk_contents);
                 $wk_contents = str_replace( $main_temp, " [ ".$main_temp." ] ",$wk_contents);
                 }
               	 else $l--;
                $main_temp2 = $main_temp;
	        }

        }

	// Magic quote anti-bug
	$wk_contents = str_replace("\\'","'",$wk_contents);
	$wk_contents = str_replace("\\\"","\"",$wk_contents);
	$wk_contents = str_replace("\\\\","\\",$wk_contents);

	
	// Backup
	if(file_exists($page_to_edit)!=false){
		copy($page_to_edit, $page_to_edit.date("U").".bak");
	}

	// Automatic trackback processing
	$tb_url = "";
		if( strpos( " ".$wk_contents, "[TB:" ) > 0 ) {
			$ii = strpos( $wk_contents, "[TB:" ) + 4;
			$ie = strpos( $wk_contents, "]", $ii);
			$tb_url = substr( $wk_contents, $ii, $ie-$ii );
			$wk_contents = str_replace( "[TB:".$tb_url."]", "[".$tb_url."]", $wk_contents );

		// Tatertools trackback
             	if( strpos( $tb_url,     "/tt/rserver.php?mode=tb&sl=") > 1 )  {
		$tb_src   =str_replace(   "/tt/rserver.php?mode=tb&sl=", "/tt/index.php?pl=", $tb_url);
		$wk_contents=str_replace(   "[".$tb_url."]",    "[".$tb_src."]", $wk_contents);
		} else

		// Tatertools trackback 2
             	if( strpos( $tb_url,     "/tt/rserver.php?mode=tb&sl=") > 1 )  {
		$tb_src   =str_replace(   "/blog/rserver.php?mode=tb&sl=", "/blog/index.php?pl=", $tb_url);
		$wk_contents=str_replace(   "[".$tb_url."]",    "[".$real_src."]", $wk_contents);
		} else

		// Tatertools 
             	if( strpos( $tb_url,     "/tt/index.php?pl=") > 1 )  
		$tb_url=str_replace(     "/tt/index.php?pl=",    "/tt/rserver.php?mode=tb&sl=", $tb_url); else

		// Tatertools  2
             	if( strpos( $tb_url,     "/blog/index.php?pl=") > 1 )  
		$tb_url=str_replace(     "/blog/index.php?pl=","/blog/rserver.php?mode=tb&sl=", $tb_url); else
             	
		// egloos trackback
		if( strpos( $tb_url,     "egloos.com/tb/") > 1 )  
		{
		$tb_src      = str_replace(     "egloos.com/tb/", "egloos.com/", $tb_url);	
		$wk_contents = str_replace( "[".$tb_url."]", "[".$tb_src."]", $wk_contents );
		} else
								
		// egloos 
             	if( strpos( $tb_url,     "egloos.com/") > 1 )    
		$tb_url=str_replace(     "egloos.com/", "egloos.com/tb/", $tb_url); else

		// WikiKiwi trackback
             	if( strpos( $tb_url,     "index.php?pagetowrite=") > 1 ) {
		$tb_src=str_replace(     "index.php?pagetowrite=", "index.php?pagetoread=", $tb_url);
		$wk_contents = str_replace( "[".$tb_url."]", "[".$tb_src."]", $wk_contents );
		} else

		// WordPress trackback
             	if( strpos( $tb_url,     "/tb/tb.php?n=") > 1 ) {
		$tb_src=str_replace(     "/tb/tb.php?n=", "/wp/archives/", $tb_url);
		$wk_contents = str_replace( "[".$tb_url."]", "[".$tb_src."]", $wk_contents );
		} else

		// WordPress trackback type2
             	if( strpos( $tb_url,     "/wp/wp-trackback.php/") > 1 ) {
		$tb_src=str_replace(     "/wp/wp-trackback.php/", "/wp/index.php?p=", $tb_url);
		$wk_contents = str_replace( "[".$tb_url."]", "[".$tb_src."]", $wk_contents );
		} else

		// blogin trackback
             	if( strpos( $tb_url,     "www.blogin.com/blog/tb/?id=") > 1 ) {
		$tb_src=str_replace(     "www.blogin.com/tb/?id=", "www.blogin.com/blog/main.php?datX=", $tb_url);
		$wk_contents = str_replace( "[".$tb_url."]", "[".$tb_src."]", $wk_contents );
		} else

		// blogin
             	if( strpos( $tb_url,     "www.blogin.com/blog/main.php?datX=") > 1 ) 
		$tb_url=str_replace(     "www.blogin.com/blog/main.php?datX=", "www.blogin.com/tb/?id=", $tb_url); else

		$tb_url="";	
		}


	// Actual wrting
	$xmlfilename = $page_to_edit.".xml";
	if(file_exists($page_to_edit)!=false){
        chmod( $page_to_edit, 0777 );
        chmod( $xmlfilename, 0777 );

	$fp = fopen( $page_to_edit, "r");
	$old_title = fgets($fp);
	$old_timestamp = fgets($fp);
	fclose($fp);

	$old_file_size = filesize( $page_to_edit );
	$new_file_size = strlen($wk_title)+strlen($old_timestamp)+strlen($wk_contents);
	}

	if(strlen($wk_title)<1) $wk_title = "".$old_title;
	$wk_title = str_replace("\n","",$wk_title);
	if(strpos($wk_title,"%u")>=1) { $wk_title=(ereg_replace('%u([[:alnum:]]{4})', '&#x\1;',"".$wk_title)); }

	$old_timestamp = str_replace("\n","",$old_timestamp);

if(strlen($wk_contents)>2 && strlen($wk_title)<40 ) { // ÇØÅ·, ½ºÆÔ ¹× Ä¡¸íÀû ¿¡·¯ ¹æÁö¸¦ À§ÇØ
	//if(strlen($HTTP_POST_VARS["name"])>1 && strpos($HTTP_POST_VARS["nospam"], "ehehe")!=1) exit(1); // spam ¹æÁö
	// if(strlen($HTTP_POST_VARS["name"])<2  
	// && strspn($wk_title."$","1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/")!=strlen($wk_title)
	//  ) {
	//	echo("Anti hacking:<BR/>".$wk_title);
	//	exit(1); // ½ºÆÔ ¹æÁö
	// }
	if(strlen($HTTP_POST_VARS["name"])<2 && strcmp($reply_edit_type,"reply")==0 ) exit(1); // spam ¹æÁö

	if( strpos($reply_edit_type,"eply")==1 ) {
		if(strpos($wk_contents,".png")>0) {echo("Image is prohibited.");exit(1);}
		if(strpos($wk_contents,".jpg")>0) {echo("Image is prohibited.");exit(1);}
		if(strpos($wk_contents,".gif")>0) {echo("Image is prohibited.");exit(1);}

		if(strpos($wk_contents,".PNG")>0) {echo("Image is prohibited.");exit(1);}
		if(strpos($wk_contents,".JPG")>0) {echo("Image is prohibited.");exit(1);}
		if(strpos($wk_contents,".GIF")>0) {echo("Image is prohibited.");exit(1);}
		}

	$fpxml = fopen( $xmlfilename, "wb");
	$fp = fopen( $page_to_edit, "wb");

	$xmltags = "<?xml version=\"1.0\" ?>\n<text><![CDATA[\n";
	fwrite($fpxml,$xmltags,strlen($xmltags) );
	if(strpos(" ".$wk_title,urldecode("%EF%BB%BF"))>0) {
		//This is for UTF-8 bug
		$wk_title = str_replace(urldecode("%EF%BB%BF"),"",$wk_title);
		}
	fwrite( $fp, $wk_title."\n");
	$writing_time_stamp = "" ;
	if($old_file_size-$new_file_size<10 && $old_file_size-$new_file_size>-10) // if it's minor change
		$writing_time_stamp = $old_timestamp."\n";
	if(strlen( $writing_time_stamp )< 2) $writing_time_stamp = "UNIX clock : ".date("U")." / Common clock ".date("Y.m.d, g:i a")."\n" ;
	fwrite( $fp, $writing_time_stamp );


	if( strcmp($reply_edit_type,"reply")==0 ) {
	// ´ä±Û ´Þ±â³ª Ãß°¡ÀÌ¸é
		if(strcmp($i,"¾Õ±Û ")==0)
			{
			$j=$j."\n";
			fwrite( $fp, $j, strlen($j) );
			$wk_contents=$wk_contents."\n - '' ".$HTTP_POST_VARS["name"]." ".date("Y.m.d, g:i a")." '' ";
			$wk_contents=$wk_contents."\n----\n";
			fwrite( $fp, $wk_contents, strlen($wk_contents) );
			fwrite( $fp, $wk_contents_ori, strlen($wk_contents_ori) );
			fwrite( $fpxml, $wk_contents, strlen($wk_contents) );
			fwrite( $fpxml, $wk_contents_ori, strlen($wk_contents_ori) );
			}
		else
		//if(strcmp($i,"´ä±Û ")==0)
			{
			$j=$j;
			if(strcmp($i,"´ä±Û")==0) {
				fwrite( $fp, $j, strlen($j) );
				if(strlpos("\n")<2) fwrite($fp,"\n");
				}

			if(strlen($HTTP_POST_VARS["name"])>1 
				// && strpos($HTTP_POST_VARS["nospam"], "ehehe")==1
				) {
				if (strlen($HTTP_POST_VARS["hompurl"])>6 )
				$wk_contents = "\n----\n".$wk_contents." - '' ".$HTTP_POST_VARS["name"]." [".$HTTP_POST_VARS["hompurl"]."] ".date("Y.m.d, g:i a")." '' ";
				else
				$wk_contents = "\n----\n".$wk_contents." - '' ".$HTTP_POST_VARS["name"]." ".date("Y.m.d, g:i a")." '' ";
			}

			fwrite( $fp, $wk_contents_ori, strlen($wk_contents_ori) );
			fwrite( $fpxml, $wk_contents_ori, strlen($wk_contents_ori) );
		
			if(strpos($wk_contents,"%u")>=1) { $wk_contents=(ereg_replace('%u([[:alnum:]]{4})', '&#x\1;',"".$wk_contents)); }

			fwrite( $fp, $wk_contents, strlen($wk_contents) );
			fwrite( $fpxml, $wk_contents, strlen($wk_contents) );
			}
	}
	else	{ // ¾Æ´Ï¸é ÀÏ¹Ý ¾²±â
		fwrite( $fp,  $wk_contents, strlen($wk_contents) );
		fwrite($fpxml,$wk_contents, strlen($wk_contents) );
	}

	$xmltags = "\n ]]></text>";
	fwrite($fpxml,$xmltags,strlen($xmltags) );

	fclose($fp);
	fclose($fpxml);

// XML link table writing
$tmp_links = "";
if(file_exists("wkctlinks.xml")!=false){
	$fp = fopen( "wkctlinks.xml", "r" );
	fgets($fp); 
	$tmp_links = fgets($fp); 
	fclose($fp);
}

$tmp_links = str_replace("\r","",$tmp_links);
$tmp_links = str_replace("\n","",$tmp_links);

$links = wkfunc_find_links_from_text($wk_contents);
$tmp_title = str_replace("\r","",$wk_title);
$tmp_title = str_replace("\n","",$tmp_title);

while(1==1){ // erasing exitsed link logs
	$tmp_needle = "a>".$tmp_title.",";
	$tmp_p = strpos($tmp_links, $tmp_needle);
	if($tmp_p<1) break;
	$tmp_pe=strpos($tmp_links, "</a>", $tmp_p)+3;
	$tmp_needle = "<".substr($tmp_links,$tmp_p,$tmp_pe-$tmp_p).">";
	$tmp_links = str_replace($tmp_needle,"",$tmp_links);
}

for($l=0;$l<count($links);$l++){ // adding new link logs
	if(strlen($links[$l])<2) continue;
	$tmp_pair = "<a>".$tmp_title.",".$links[$l]."</a>";
	$tmp_links=$tmp_links.$tmp_pair;
}
$fp = fopen("wkctlinks.xml","wb");
fwrite($fp, "<?xml version=\"1.0\" encoding=\"".$m_global_encoding."\"?><xml>\n");
fwrite($fp,  $tmp_links);
fwrite($fp,  "\n</xml>\n");
fclose($fp);

	//
	// XML list setup
	//
// ### Ajax static backup ÆäÀÌÁö¸¦ À§ÇÑ XML ÆÄÀÏ¸®½ºÆ®ÀÇ ÀÛ¼º ########################################################
	$xmllist = "";
	if(file_exists("wkctlist.xml")!=false){
	$fp = fopen( "wkctlist.xml", "rb" );
        $xmllist = fread( $fp, filesize( "wkctlist.xml" ) );
	fclose( $fp );

	$xmllist = str_replace( "\r\n", "\n", $xmllist);
	$xmllist = str_replace( "<?xml version=\"1.0\" encoding=\"".$m_global_encoding."\"?>\n<xml>", "", $xmllist );
	$xmllist = str_replace( "</xml>", "", $xmllist );
	}

	$wk_title_url = urlencode($wk_title);

	if( strpos( $xmllist, $xmlfilename ) >1 ) {
		$deletion_i = strpos( $xmllist, "<title>".$wk_title."</title>" );
		$deletion_e = strpos( $xmllist, "</file>", $deletion_i+1 ) + 7;
		if($deletion_i>2 && $deletion_e>$deletion_i) $deletionary =substr( $xmllist, $deletion_i, $deletion_e-$deletion_i );
		if($deletion_i>2 && $deletion_e>$deletion_i) $xmllist = str_replace($deletionary,"",$xmllist);
		$deletion_i = strpos( $xmllist, "<title>".$wk_title_url."</title>" );
		$deletion_e = strpos( $xmllist, "</file>", $deletion_i+1 ) + 7;
		if($deletion_i>2 && $deletion_e>$deletion_i) $deletionary =substr( $xmllist, $deletion_i, $deletion_e-$deletion_i );
		if($deletion_i>2 && $deletion_e>$deletion_i) $xmllist = str_replace($deletionary,"",$xmllist);		
		}

	$xmllist = $xmllist."<title>".$wk_title."</title><time>".$writing_time_stamp."</time><file>".$xmlfilename."</file>";
	$xmllist = $xmllist."<title>".$wk_title_url."</title><time>".$writing_time_stamp."</time><file>".$xmlfilename."</file>";
	
	$fpxml = fopen( "wkctlist.xml", "wb");
	$xmltags = "<?xml version=\"1.0\" encoding=\"".$m_global_encoding."\"?>\n<xml>".$xmllist."</xml>";
	fwrite($fpxml,$xmltags,strlen($xmltags) );
	fclose($fpxml);
	
// ### RSS feed¸¦ ¸¸µç´Ù. ############################################################################################
// RSS Feeding
// for blog RSS


$main_temp = wkfunc_newest_file_find(0);
$time_i = strpos($main_temp, "UNIX" );
$time_j = strpos($main_temp, " : ", $time_i)+3;
$time_k = strpos($main_temp, " ", $time_j);
$css_newest_time_stamp = 0 + (substr($main_temp, $time_j, $time_k-$time_j));
$css_newest_time = date("D, d M Y H:i:s O", $css_newest_time_stamp);
$css_URL_title_URI = $_SERVER['REQUEST_URI'];
$css_URL_title_hostname = $GLOBALS['HTTP_HOST'];
$css_URL_title = $css_URL_title_hostname . substr($css_URL_title_URI, 0, strpos($css_URL_title_URI,"index.php"));
$wk_date ="";
$wk_contents = "<?xml version=\"1.0\" encoding=\"".$m_global_encoding."\"?>\n";
$wk_contents = $wk_contents."<rss version=\"2.0\"><channel><title>".$wk_first_page_title."</title>\n";
$wk_contents = $wk_contents."<link>http://".$css_URL_title."</link>\n";
$wk_contents = $wk_contents."<description>RSS generated from WikiKiwi</description>\n";
$wk_contents = $wk_contents."<language>ko</language>\n";
$wk_contents = $wk_contents."<pubDate>$css_newest_time</pubDate>\n";

$main_temp2 = "";
$i = 0;

for($l=0;$l<7;$l++){
        $main_temp = wkfunc_newest_file_find($i);
        if(strcmp($main_temp,$main_temp2)==0 || strlen($main_temp)<2 ) break;
        $j = strpos($main_temp,":");
        $k = strpos($main_temp,"#CQSW?=");
        $time_i = strpos($main_temp, "UNIX");
        $time_j = strpos($main_temp, " : ", $time_i)+3;
        $time_k = strpos($main_temp, " ", $time_j);
        $css_time_stamp = 0 + (substr($main_temp, $time_j, $time_k-$time_j));
        $css_time = date("D, d M Y H:i:s O", $css_time_stamp);
	$wk_first_page_title_noret = "".$wk_first_page_title;
	$wk_first_page_title_noret = str_replace("\n","",$wk_first_page_title_noret);
	$wk_first_page_title_noret = str_replace("\r","",$wk_first_page_title_noret);
	$fixedtitle = "";
	if(strcmp($main_temp,"»èÁ¦")!=0) $fixedtitle =  substr($main_temp,$j+1,$k-$j-1);
        if(strcmp($main_temp,"»èÁ¦")!=0 && strcmp($fixedtitle,$wk_first_page_title_noret)!=0)
        {
                $wk_contents = $wk_contents."<item>";
                $wk_contents = $wk_contents."<title>".$fixedtitle."</title>\n";
                $wk_contents = $wk_contents."<link>http://".$css_URL_title.wikiencode("wkct_".substr($main_temp,$j+1, $k-$j-1).".htm")."</link>\n";




$tmp_filename_to_edit = wkfunc_file_find_from_title( substr($main_temp,$j+1, $k-$j-1) );

$fp = fopen( $tmp_filename_to_edit, "r" );
$tmp_wk_title = fgets($fp);
$tmp_wk_date = fgets($fp);
$tmp_wk_contents = fread( $fp, filesize( $tmp_filename_to_edit ) - strlen($tmp_wk_title) - strlen($tmp_wk_date));
fclose( $fp );
$tmp_wk_contents = str_replace("<", "&lt;",   $tmp_wk_contents);
$tmp_wk_contents = str_replace(">", "&gt;",   $tmp_wk_contents);
$tmp_wk_contents = str_replace("\n","<BR>", $tmp_wk_contents);
$jj = strlen($tmp_wk_contents);
for($ii=0;$ii<$jj;$ii++){
	$kk=strpos($tmp_wk_contents,"[http://",$ii);
	if($kk != false)
	{
		$ll=strpos($tmp_wk_contents,"]",$kk);
                $deco_url = substr($tmp_wk_contents,$kk+1,$ll-$kk-1);
		if(strpos($deco_url,".jpg")!=false) $tmp_wk_contents = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$tmp_wk_contents); else
		if(strpos($deco_url,".gif")!=false) $tmp_wk_contents = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$tmp_wk_contents); else
		if(strpos($deco_url,".png")!=false) $tmp_wk_contents = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$tmp_wk_contents);
		$ii = $kk+4;
	} else if($ii>0) break;
}





                $wk_contents = $wk_contents."<description>\n<![CDATA[\n".$tmp_wk_contents."\n]]>\n</description>\n";
                $wk_contents = $wk_contents."<category>none</category>\n";
                $wk_contents = $wk_contents."<authour>WikiKiwi</authour>\n";
                $wk_contents = $wk_contents."<pubDate>".$css_time."</pubDate></item>\n";

		if(strpos($tmp_wk_contents,"<BR>=== ")>0) {
		$I_st = strpos($tmp_wk_contents,"<BR>=== ");
		$I_ed = strpos($tmp_wk_contents," ===",$I_st);
		$bartitle = substr($tmp_wk_contents, $I_st+8, $I_ed-8-$I_st);
		$wk_contents = str_replace("<title>".$fixedtitle."</title>\n", "<title>\n<![CDATA[\n".$fixedtitle.":".$bartitle."\n]]>\n</title>\n", $wk_contents);
		}
        }
        else $l--;
        $i  = 0+substr($main_temp,0,$j);
        $main_temp2 = $main_temp;
        }

$wk_contents = $wk_contents."</channel></rss>\n";
$fp = fopen( "index.xml", "wb");
fwrite( $fp, $wk_contents);
fclose($fp);
}
// ### RSS ¸¸µé±â ¿Ï·á ###############################################################################################


// ### ÀÛ¾÷ ¿Ï·áÈÄ Å¬¶óÀÌ¾ðÆ®¿¡ Ç¥½ÃÇÒ ¸Þ¼¼Áöµé ######################################################################
	$tb_url = urlencode( $tb_url );
	$page_to_read = $wk_title;
	if(strpos($HTTP_POST_VARS["url"],"://")>0 || strcmp($HTTP_GET_VARS["xml"],"yes")==0) { // track backÀ» º¸³»°Å³ª xml·Î ´äÇØ ¿À¶ó´Â ¿äÃ»ÀÌ ÀÖ¾ú´Ù¸é, ±×´ë·Î.
	echo('<?xml version="1.0" encoding="iso-8859-1" ?><response><error>0</error></response>');
	} else
	{
	if(strlen($tb_url)>3) 
		echo("<script language='javascript'>location.replace('index.php?pagetoread=$wk_title&option=trackback&target=".$tb_url."');\n</script>");
		else
		if(strlen($HTTP_POST_VARS["homeurl"])<2) // ÀÌµµ Àúµµ ¾Æ´Ñ °æ¿ì homeurlÀÌ¶ó´Â º¯¼ö·Î ³Ñ¾î¿Â ÁÖ¼Ò È¤Àº, ¾²±â ¿Ï·áÇÑ ¹®Á¦ÀÇ ÆäÀÌÁö·Î ¸®´ÙÀÌ·º¼Ç
			echo("<script language='javascript'>\nlocation.replace('index.php?option=edited&pagetoread=".urlencode($wk_title)."');\n</script>");
			else
			echo("<script language='javascript'>\nlocation.replace('".$HTTP_POST_VARS["homeurl"]."');\n</script>");
	}
	exit();
}

// ### ½ÇÁ¦ ÆÄÀÏ¿¡ ¾²±â °ü·Ã ºÎºÐ ¿Ï·á ###############################################################################




// ### ÆäÀÌÁö ·»´õ¸µ ºÎºÐ ############################################################################################
$nondeco_wk2= "";

// If there is NOT any command then "read page 0"
if( strlen($page_to_read) < 1) $page_to_read = $HTTP_GET_VARS["pagetoread"];
if( strlen($page_to_read) < 1) $page_to_read = $HTTP_POST_VARS["pagetoread"];
	// If it's functional page
if( strlen($page_to_read) < 1 || strcmp($page_to_read,"FIRSTPAGE")==0) { $filename_to_read="wkct0.txt"; }
else if( strcmp($page_to_read,"TITLELIST")==0) {$wk_title = "Title List";}
else if( strcmp($page_to_read,"SRESULT")==0) {$wk_title = "Search Result";}
else if( strcmp($page_to_read,"SEARCH")==0) {$wk_title = "Search";}
else if( strcmp($page_to_read,"UPDATELIST")==0) {$wk_title = "Updated Pages";}
else if( strcmp($page_to_read,"UPLOADED")==0) {$wk_title = "Upload Completed";}
else if( strcmp($page_to_read,"UPLOAD")==0) {$wk_title = "File Upload";}

else	// If not
{
	$filename_to_read = wkfunc_file_find_from_title($page_to_read);
	if(strpos($page_to_read,"kct")>0 && strpos($page_to_read,".txt")>0 ) {
		$filename_to_read = "".$page_to_read;
		$page_to_read = wkfunc_file_find_from_title($page_to_read);
		$wk_title = $page_to_read;
	}

	if(strpos($filename_to_read,"_NONEXISTANCE__")!=false)
	{ // no existance then create new file
echo "<script language='javascript'>location.replace('index.php?pagetoedit=".$page_to_read."&option=oekaki');</script>\n";

echo $wk_head;
echo "<form method=post action=index.php>\n";
echo "<TEXTAREA name=contents rows=30 cols=90>\n";
echo $HTTP_GET_VARS["defaulttext"];
echo "</TEXTAREA><BR>\n";
$filename_to_edit = "wkct".substr($filename_to_read,16).".txt";
echo "<input type=hidden name=pagetowrite value=\"$filename_to_edit\">\n";
echo "<input type=hidden name=itstitle value=\"$page_to_read\">\n";
echo "<input type=submit value=\"¿Ï·á\">\n";
echo "<BR><input type=checkbox name=autolinkgen value=false>ÀÚµ¿¸µÅ©»ý¼º\n";
echo "</form>";
echo $wk_tail;

exit();
	}
}

// Read page contents
// If it's special function page
// Titlelist
if(strcmp($wk_title,"Á¦¸ñ ¼ø¼­ ¸ñ·Ï")==0 || strcmp($wk_title,"Title List")==0) {
$wk_date ="";
$wk_contents = "\n";
$main_temp2 = "";
$i = 0;

/*
for($l=0;$l<1500;$l++){
	$main_temp = wkfunc_first_title_file_find($main_temp2);
	if(strcmp($main_temp,$main_temp2)==0 || strlen($main_temp)<1 ) {break;}
	if(strcmp($main_temp,"»èÁ¦")!=0 ) {
		$wk_contents = $wk_contents." *  [ ".$main_temp." ]  \n";
	}
	 else $l--;
	$main_temp2 = $main_temp;
	}
*/
$wk_first_page_noret = str_replace("\n","",$wk_first_page_title);
$wk_first_page_noret = str_replace("\r","",$wk_first_page_noret);
$wk_contents = $wk_contents.wkfunc_make_title_sorted_list();
$wk_contents = str_replace( " *   [ ".$wk_first_page_noret." ]  ", " *   ".$wk_first_page_noret."(first page)  ", $wk_contents);

}



else if(strcmp($wk_title,"°Ë»ö °á°ú")==0 || strcmp($wk_title,"Search Result")==0) {
$wk_date ="";
$wk_contents = "\n";
$main_temp2 = "";
$main_title_temp = "";
$main_contents_temp = "";
$diff_temp = 0;
$i = 0;
$sphrase = $HTTP_GET_VARS["sphrase"];

$wk_contents = $wk_contents."Keyword: `` ".$sphrase." ''\n\n";

// Title Search
for($l=0;$l<1500;$l++){
	$main_temp = wkfunc_first_title_file_find($main_temp2);
	if(strcmp($main_temp,$main_temp2)==0 || strlen($main_temp)<1 ) break;
	if(strcmp($main_temp,"»èÁ¦")!=0){
		if ( strlen( $main_temp ) != strlen(str_replace($sphrase, "",$main_temp) )  )
		{	$wk_contents = $wk_contents." *  [ ".$main_temp." ]  \n";}
                } else $l--;
	$main_temp2 = $main_temp;
        }
// Contents Search
$wk_date ="";
$wk_contents = $wk_contents."\n";
$main_temp2 = "";
$main_title_temp = "";
$main_contents_temp = "";
$diff_temp = 0;
$i = 0;
for($l=0;$l<1500;$l++){
	$main_temp = wkfunc_first_title_file_find($main_temp2);
	if(strcmp($main_temp,$main_temp2)==0 || strlen($main_temp)<1 ) break;
	if(strcmp($main_temp,"»èÁ¦")!=0)
	{

               $filename_to_search = wkfunc_file_find_from_title($main_temp);
               $fp = fopen( $filename_to_search, "r" );
               $srcwk_title = fgets($fp);
               $srcwk_date = fgets($fp);
               $srcwk_contents = fread( $fp, filesize( $filename_to_search ) - strlen($srcwk_title) - strlen($srcwk_date));
               fclose( $fp );
               $srcwk_contents = " ".str_replace("\r","",$srcwk_contents);
               $srcwk_contents = str_replace("\n","",$srcwk_contents);
               $src_pos = strpos($srcwk_contents,$sphrase);
               if( $src_pos ) {
                  $wk_contents = $wk_contents." *  [ ".$main_temp." ]  \n at ".$src_pos."byte. \n\n";
                }
	} else $l--;
	$main_temp2 = $main_temp;
	}


}

else if(strcmp($wk_title,"°Ë»ö")==0  || strcmp($wk_title,"Search")==0) {
$wk_contents = $wk_contents.'<form action="index.php" method="GET">';
$wk_contents = $wk_contents.'<input name="pagetoread" type="hidden" value="SRESULT">';
$wk_contents = $wk_contents.'<input name="sphrase" type="text">';
$wk_contents = $wk_contents.'<input type="submit" value="°Ë»ö">';
$wk_contents = $wk_contents.'</form>';
}


else if(strcmp($wk_title,"ÆÄÀÏ ¾÷·Îµå")==0  || strcmp($wk_title,"File Upload")==0) {
$wk_contents = $wk_contents.'<form enctype="multipart/form-data" action="index.php" method="POST">';
$wk_contents = $wk_contents.'<input name="pagetoread" type="hidden" value="UPLOADED">';
$wk_contents = $wk_contents.'<input name="userfile" type="file">';
$wk_contents = $wk_contents.'<input type="submit" value="¾÷·Îµå">';
$wk_contents = $wk_contents.'</form>';
}

else if(strcmp($wk_title,"ÆÄÀÏ ¾÷·Îµå ¿Ï·á")==0  || strcmp($wk_title,"Upload Completed")==0) {
	$uploaddir = './';
	$uploadfile = $uploaddir. $_FILES['userfile']['name'];

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		$wk_contents = $wk_contents."ÆÄÀÏÀÌ Á¸ÀçÇÏ°í, ¼º°øÀûÀ¸·Î ¾÷·Îµå µÇ¾ú½À´Ï´Ù.";
		$wk_contents = $wk_contents."Ãß°¡ µð¹ö±ë Á¤º¸ÀÔ´Ï´Ù:\n";
                echo("<!--");print_r($_FILES);echo("-->");
	} else {
		$wk_contents = $wk_contents."ÆÄÀÏ ¾÷·Îµå °ø°ÝÀÇ °¡´É¼ºÀÌ ÀÖ½À´Ï´Ù! µð¹ö±ë Á¤º¸ÀÔ´Ï´Ù:\n";
                echo("<!--");print_r($_FILES);echo("-->");
	}

	$wk_contents = $wk_contents."<BR>\n * <a href='javascript:history.go(-2)'>ÀÌÀü ÆäÀÌÁö·Î</a>";
}

//  Update List
else if(strcmp($wk_title,"ÃÖ±Ù ¾÷µ¥ÀÌÆ® ÆäÀÌÁö")==0  || strcmp($wk_title,"Updated Pages")==0) {
$wk_date ="";
$wk_contents = "\n";
$main_temp2 = "";
$i = 0;
$wk_first_page_noret = str_replace("\n","",$wk_first_page_title);
$wk_first_page_noret = str_replace("\r","",$wk_first_page_noret);

for($l=0;$l<20;$l++){
	$main_temp = wkfunc_newest_file_find($i);
	if(strcmp($main_temp,$main_temp2)==0 || strlen($main_temp)<2 ) break;
	$j = strpos($main_temp,":");
	$k = strpos($main_temp,"#CQSW?=");

	$wk_link_page = substr($main_temp,$j+1,$k-$j-1);
	if(strpos(" ".$main_temp,":»èÁ¦")<=0)
	  {$wk_contents = $wk_contents." &nbsp; [LINK:moindiff.gif] [ ".$wk_link_page." ]  &nbsp;&nbsp; ".substr($main_temp,strpos($main_temp," 20", strpos($main_temp,"UNIX") ) );}
       	else $l--;

	$i  = 0+substr($main_temp,0,$j);
	$main_temp2 = $main_temp;
	}

$wk_contents = str_replace( " [LINK:moindiff.gif] [ ".$wk_first_page_noret." ]  &nbsp;", " [LINK:moindiff.gif]  First page &nbsp;", $wk_contents);
}

// If it's not
else if( strcmp( $HTTP_GET_VARS["option"], "history" )==0 ) { // history view
	$fp = fopen( $filename_to_read, "r" );
	$wk_title = fgets($fp);
	$wk_date = fgets($fp);
	fclose( $fp );
	$wk_contents = "\n".wkfunc_backup_file_find_from_filename($filename_to_read,0);
}
else if( strcmp( $HTTP_GET_VARS["option"], "diff" )==0 ) { // diff
	$fp = fopen( $filename_to_read, "r" );
	$wk_title = fgets($fp);
	$wk_date = fgets($fp);
	$wk_contents1 = fread( $fp, filesize( $filename_to_read ) - strlen($wk_title) - strlen($wk_date));
	$wk_contents1 = str_replace("\r","",$wk_contents1);
	fclose( $fp );

	$backup_fn = $HTTP_GET_VARS["backupfn"];
	if(strlen($backup_fn)<1) $backup_fn = wkfunc_backup_file_find_from_filename($filename_to_read,1);
	if(strpos($backup_fn,".txt0.bak")>0) $backup_fn = $filename_to_read;

	$fp = fopen($backup_fn , "r" );
	fgets($fp);
	fgets($fp);
	$wk_contents2 = fread( $fp, filesize( $backup_fn ) - strlen($wk_title) - strlen($wk_date));
	$wk_contents2 = str_replace("\r","",$wk_contents2);
	fclose( $fp );

	$lines2 = explode("\n",$wk_contents2);

	$wk_contents2 = str_replace(" ","",$wk_contents2);
	$wk_contents2 = str_replace("\r","",$wk_contents2);
	$wk_contents2 = str_replace("\n","",$wk_contents2);

	$lines = explode("\n",$wk_contents1);
	$nondeco_wk2 = "<h2>¼öÁ¤ ¹× Ãß°¡µÈ ºÎºÐ Ç¥½Ã</h2><BR>";
	for($l=0;$l<count($lines);$l++){
		$line = $lines[$l];
		$line = str_replace(" ","",$line);
		$line = str_replace("\r","",$line);
		if( strlen($line)<2 )
			;
		else
		if( strpos(" ".$wk_contents2,$line)>0 )
			$nondeco_wk2= $nondeco_wk2."<font size=2>".$lines[$l]."</font><BR>\n";
		else
			$nondeco_wk2= $nondeco_wk2."<strong>".$lines[$l]."</strong><BR>\n";
	}

	$wk_contents1 = str_replace(" ","",$wk_contents1);
	$wk_contents1 = str_replace("\r","",$wk_contents1);
	$wk_contents1 = str_replace("\n","",$wk_contents1);
	$nondeco_wk2 = $nondeco_wk2."<BR><h2>¼öÁ¤ ÀÌÀü ¹öÀüÀÇ º¯°æ ºÎºÐ</h2><BR>";
	for($l=0;$l<count($lines2);$l++){
		$line = $lines2[$l];
		$line = str_replace(" ","",$line);
		$line = str_replace("\r","",$line);
		if( strlen($line)<2 )
			;
		else
		if( strpos(" ".$wk_contents1,$line)>0 )
			;
		else
			$nondeco_wk2= $nondeco_wk2."<strong>".$lines2[$l]."</strong><BR><BR>\n";
	}

	//$wk_contents1."<BR><BR>".$wk_contents2;
	$wk_contents = " -==NonDeco2==- ";
}
else {
$fp = fopen( $filename_to_read, "r" );
$wk_title = fgets($fp);
$wk_date = fgets($fp);
$wk_contents = fread( $fp, filesize( $filename_to_read ) - strlen($wk_title) - strlen($wk_date));
$wk_contents = str_replace("\r","",$wk_contents);
fclose( $fp );
}

// Password processing
$i = substr($wk_contents,0,5);
if( strcmp($i,"¾ÏÈ£ ")==0 || strcmp($i,"¾Õ±Û ")==0  || strcmp($i,"´ä±Û ")==0 ) {
	$i = strpos($wk_contents,"\n");
	$wk_contents = substr($wk_contents,$i+1);
}

	// Contents Designize
		// inclusion
$include_title = "nothing";
if( strpos($wk_contents,"[IncludeUpdate") != false )
{
        $ii  = strpos($wk_contents,"[IncludeUpdate");
        $ii += 15;
        $ij  = strpos($wk_contents,"]", $ii);
        $include_timestamp= substr($wk_design, $ii, $ij-$ii);

        $main_temp = wkfunc_newest_file_find($include_timestamp);
        $ij = strpos($main_temp,":");
        $ik = strpos($main_temp,"#CQSW?=");
        $include_title = substr($main_temp,$ij+1,$ik-$ij-1); 
        $include_time = substr($main_temp,$ik+7);
        $include_timestamp  = 0+substr($main_temp,0,$ij);


	if(strcmp( str_replace("\n","",str_replace("\r","",$include_title)),str_replace("\n","",str_replace("\r","",$wk_title)) )==0) {
	// If the newest one is itself, then pass it, too.
        $main_temp = wkfunc_newest_file_find($include_timestamp);
        $ij = strpos($main_temp,":");
        $ik = strpos($main_temp,"#CQSW?=");
        $include_title = substr($main_temp,$ij+1,$ik-$ij-1);
        $include_time = substr($main_temp,$ik+7);
        $include_timestamp  = 0+substr($main_temp,0,$ij);
	}

	if(strpos($include_title,"ikiSandBox")==1 || strpos($include,"±â¸Þ¸ð¿Í Å×½ºÆ®")>0) {
	// If it is WikiSandBox, then pass it.
        $main_temp = wkfunc_newest_file_find($include_timestamp);
        $ij = strpos($main_temp,":");
        $ik = strpos($main_temp,"#CQSW?=");
        $include_title = substr($main_temp,$ij+1,$ik-$ij-1);
        $include_time = substr($main_temp,$ik+7);
        $include_timestamp  = 0+substr($main_temp,0,$ij);
	}

        $inc_filename_to_read = wkfunc_file_find_from_title($include_title);
$fp = fopen( $inc_filename_to_read, "r" );
$inc_wk_title = fgets($fp); 
$inc_wk_title = str_replace("\r","",$inc_wk_title);
$inc_wk_title = str_replace("\n","",$inc_wk_title);
$inc_wk_date = fgets($fp);
$inc_wk_firstline = " ".fgets($fp);
if(strpos($inc_wk_firstline,"¾Õ±Û ")==1 || strpos($inc_wk_firstline,"´ä±Û ")==1)
	  $inc_wk_firstline=""; else $inc_wk_firstline = $inc_wk_firstline."\n";
$inc_wk_contents = $inc_wk_firstline . fread( $fp, filesize( $inc_filename_to_read ) - strlen($inc_wk_title) - strlen($inc_wk_date));
$inc_wk_contents = str_replace("\r","",$inc_wk_contents);
fclose( $fp );
        $inc_wk_contents = str_replace("[IncludeUpdate]","",$inc_wk_contents);

        $inclusion = "\n=== RecentUpdate: [ ".$inc_wk_title." ]  ===\n".$inc_wk_contents;
        $wk_contents = str_replace("[[IncludeUpdate".$time."]]",$inclusion, $wk_contents);
	$wk_title = $inc_wk_title;
	$filename_to_read = $inc_filename_to_read;
	$wk_date = $inc_wk_date;
}

		// non-decoratable processing
	$k=strpos($wk_contents,"\n{{{\n");
	$nondeco = "";
	if($k != false)
	{
		$l=strpos($wk_contents,"\n}}}\n");
		$nondeco =  substr($wk_contents,$k+5, $l-$k-5);
		$wk_contents = substr($wk_contents,0,$k)."--==NONDECO==--".substr($wk_contents,$l+5);
        }

		// Processing contents
if(
strcmp($wk_title,"ÆÄÀÏ ¾÷·Îµå")!=0 && strcmp($wk_title,"ÆÄÀÏ ¾÷·Îµå ¿Ï·á")!=0 && strcmp($wk_title,"°Ë»ö")!=0 && 
strcmp($wk_title,"File Upload")!=0 && strcmp($wk_title,"Upload Completed")!=0 && strcmp($wk_title,"Search")!=0
) {
$wk_contents = str_replace("<","&lt;",$wk_contents);
$wk_contents = str_replace(">","&gt;",$wk_contents);
$wk_contents = str_replace("\n","<BR>\n",$wk_contents);
}

if(strpos($HTTP_GET_VARS["option"], "rackback")==1){ // processing trackback
$css_URL_title_URI = $_SERVER['REQUEST_URI'];
$css_URL_title_hostname = $GLOBALS['HTTP_HOST'];
$css_URL_title = $css_URL_title_hostname . substr($css_URL_title_URI, 0, strpos(
$css_URL_title_URI,"index.php"));

echo $wk_head;
echo '<form name=trackback method=post action="http://__">';
echo 'Æ®·¢¹é º¸³¾ ÁÖ¼Ò <input name=actiontarget type=text size=60 value="'.$HTTP_GET_VARS["target"].'"><BR>';
echo 'Á¦¸ñ <input name=title type=text value="'.$wk_title.'">';
echo '<input name=blog_name type=hidden value="'.$wk_first_page_title.'"><br>';
echo '³»¿ë¿ä¾à <textarea name=excerpt>more...</textarea><BR>';
echo '<input name=url type=hidden value="http://'.$css_URL_title."index.php?pagetoread=".urlencode($page_to_read).'">';
echo '<input name=go value="º¸³»±â" type=button onclick="gotrackback()">';
echo '</form>';
echo '<script language="javascript">';
echo 'function gotrackback(){';
echo 'document.trackback.action=document.trackback.actiontarget.value;';
echo 'document.trackback.submit();';
echo '}';
echo '</script>';
echo $wk_tail;
exit(0);
}

		// merging
// back link parsing
$converting_back_links = "";
$tmp_title = str_replace("\r","",$wk_title);
$tmp_title = str_replace("\n","",$tmp_title);
if(file_exists("wkctlinks.xml")!=false && strpos($wk_design,"¿ª¸µÅ©µéÇ¥½Ã")>0){
	$fp = fopen( "wkctlinks.xml", "r" );
	fgets($fp); 
	$tmp_links = fgets($fp); 
	fclose($fp);

	$l=0;
	$links = explode("/a>",$tmp_links);
	for($l=0;$l<count($links);$l++){
	$l2 = strpos($links[$l],",".$tmp_title."<");
	if($l2==false) continue;
	$tmp_link = substr($links[$l], strpos($links[$l],"<a>")+3) ;
	$tmp_link = substr($tmp_link,0,strpos($tmp_link,",") );
	if(strpos($converting_back_links,"[".$tmp_link."]")>0) continue;
	$converting_back_links = $converting_back_links." [".$tmp_link."] ";
	}
}

$wk_design = $wk_head.$wk_contents.$wk_tail;
                        // New line processing
			// Special Date processing
$wk_design = str_replace("¸¶Áö¸·º¯°æÀÏ½Ã",$wk_date, $wk_design);
$wk_design = str_replace("LastEditedTime",$wk_date, $wk_design);
			// Backlink processing
$wk_design = str_replace("¿ª¸µÅ©µéÇ¥½Ã",$converting_back_links,$wk_design);
			// Very Special Thing - costomizable
$wk_design = str_replace("''''''","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$wk_design);
                                        // For no-smok style
$wk_design = str_replace("\n==== ",$deco_headline_start." ", $wk_design);
$wk_design = str_replace(" ====<BR>",$deco_headline_end." ", $wk_design);
$wk_design = str_replace("\n=== ",$deco_headline_start." ", $wk_design);
$wk_design = str_replace(" ===<BR>",$deco_headline_end, $wk_design);
$wk_design = str_replace("\n== ",$deco_headline_start." ", $wk_design);
$wk_design = str_replace(" ==<BR>",$deco_headline_end, $wk_design);
$wk_design = str_replace(" {{|","[[[---Y<BR>", $wk_design);
$wk_design = str_replace("\n{{|","[[[---Y<BR>", $wk_design);
$wk_design = str_replace("|}}<BR>","Y---]]]<BR>", $wk_design);
$wk_design = str_replace("|}} ","Y---]]]<BR>", $wk_design);

                                        // Original Wikikiwi
$wk_design = str_replace("\n * ",$deco_blit_start." ",$wk_design);
$wk_design = str_replace("\n  * ",$deco_blit_start." ",$wk_design);
$wk_design = str_replace("[[[ ",$deco_headline_start." ", $wk_design);
$wk_design = str_replace(" ]]]"," ".$deco_headline_end, $wk_design);
$wk_design = str_replace("[[[---Y<BR>","<table style='background-color: #EEEEC0;text-align:left;padding: 5px;margin: 0px 10px;'><tr style='background-color: #FFFFE0;text-align:left;padding: 5px;margin: 0px 10px;'><td style='background-color: #EEEEC0;text-align:left;padding: 5px;margin: 0px 10px;'><font color=black>",$wk_design);
$wk_design = str_replace("Y---]]]<BR>","</font></td></tr></table>",$wk_design);
$wk_design = str_replace("[[[---G<BR>","<table style='background-color: #CCFFCC;text-align:left;padding: 5px;margin: 0px 10px;'><tr style='background-color: #CCFFCC;text-align:left;padding: 5px;margin: 0px 10px;'><td style='background-color: #CCFFCC;text-align:left;padding: 5px;margin: 0px 10px;'><font color=black>",$wk_design);
$wk_design = str_replace("G---]]]<BR>","</font></td></tr></table>",$wk_design);
$wk_design = str_replace("[[[---B<BR>","<table style='background-color: #CCFFFF;text-align:left;padding: 5px;margin: 0px 10px;'><tr style='background-color: #CCFFFF;text-align:left;padding: 5px;margin: 0px 10px;'><td style='background-color: #CCFFFF;text-align:left;padding: 5px;margin: 0px 10px;'><font color=black>",$wk_design);
$wk_design = str_replace("B---]]]<BR>","</font></td></tr></table>",$wk_design);
$wk_design = str_replace("[[[---F<BR>","<table style='background-color: #FFCCCC;text-align:left;padding: 5px;margin: 0px 10px;'><tr style='background-color: #EEF2CB;text-align:left;padding: 5px;margin: 0px 10px;'><td style='background-color: #FFCCCC;text-align:left;padding: 5px;margin: 0px 10px;'><font color=black>",$wk_design);
$wk_design = str_replace("F---]]]<BR>","</font></td></tr></table>",$wk_design);
$wk_design = str_replace("[[[---<BR>","<table style='background-color: #EEF2CB;text-align:left;padding: 5px;margin: 0px 10px;'><tr style='background-color: #EEF2CB;text-align:left;padding: 5px;margin: 0px 10px;'><td style='background-color: #EEF2CB;text-align:left;padding: 5px;margin: 0px 10px;'><font color=black>",$wk_design);
$wk_design = str_replace("---]]]<BR>","</font></td></tr></table>",$wk_design);



                                        // Macro
		// replay link macro
if( (strcmp($filename_to_read,"wkct0.txt")==0 || strcmp($HTTP_GET_VARS["pagetoread"],"FIRSTPAGE")==0) && strcmp($include_title,"nothing")!=0)
	{
	$wk_design = str_replace("µ¡±Û´Þ±â¸µÅ©","index.php?pagetoedit=".$include_title."&edittype=reply",$wk_design);
	$include_title = str_replace("\r", "", $include_title);
	$include_title = str_replace("\n", "", $include_title);
	$wk_design = str_replace("ReplyPageLink","index.php?pagetoedit=".urlencode($include_title)."&edittype=reply",$wk_design);
	}
else
	{
	$wk_design = str_replace("µ¡±Û´Þ±â¸µÅ©","index.php?pagetoedit=".$wk_title."&edittype=reply",$wk_design);
	$wk_title = str_replace("\r","",$wk_title);
	$wk_title = str_replace("\n","",$wk_title);
	$wk_design = str_replace("ReplyPageLink","index.php?pagetoedit=".urlencode($wk_title)."&edittype=reply",$wk_design);
	}


		// last updated link macro
if( strpos($wk_design,"[LastLink]") != false )
{
        $ii = strpos($wk_date,":")+1;
        $ie = strpos($wk_date," /");
        $include_timestamp=substr($wk_date, $ii, $ie-$ii);
        $main_temp = wkfunc_newest_file_find($include_timestamp);
        $ij = strpos($main_temp,":");
        $ik = strpos($main_temp,"#CQSW?=");
        $include_title = substr($main_temp,$ij+1,$ik-$ij-1);
        $include_timestamp  = 0+substr($main_temp,0,$ij);

        $inclusion = "<a href=\"index.php?pagetoread=".($include_title)."\">".$include_title."</a>";
        $wk_design = str_replace("[[LastLink]]", $inclusion, $wk_design);
}

$css_URL_title_URI = $_SERVER['REQUEST_URI'];
$css_URL_title_hostname = $GLOBALS['HTTP_HOST'];
$css_URL_title = $css_URL_title_hostname . substr($css_URL_title_URI, 0, strpos($css_URL_title_URI,"index.php"));
$wk_design = str_replace("Æ®·¢¹éÁÖ¼ÒÇ¥½Ã", "http://".$css_URL_title."index.php?pagetowrite=".$filename_to_read, $wk_design);

if( strpos($wk_design,"[TableOfContents]") != false )
 {
        $j = strlen($wk_design);
        $c = 1;
        $tt = "";

        for($i=0;$i<$j;$i++){
           	$st=strpos($wk_design,$deco_headline_start,$i);
           	$ed=strpos($wk_design,$deco_headline_end,$i);

            	if($st != false)
             	{
             	      $st += strlen( $deco_headline_start );
                      $tt = $tt ." &nbsp;&nbsp;&nbsp; ". $deco_blit_start . $c .". &nbsp; <a href='#'>". substr( $wk_design , $st , $ed - $st ) . "</a>";
             	      $i = $ed + 1;
             	      $c ++;
                }
                else
                break;
        }

        $wk_design = str_replace("[[TableOfContents]]",$tt, $wk_design);
}


if( strpos($wk_design, "[[RSS:") >0 ) { // RSS SisterWiki macro processing  -----
$RSSSisterWiki = '
<!-- javascript to printing RSS update list from InternetExplorerRssReader -->
<script language="javascript">

                                                   total_domains    = new Array(); total_titles     = new Array(); total_link_posts = new Array(); total_dates      = new Array(); total_clocks     = new Array(); total_counter    = 0;var m_err_msg = ""; // err_msg global variable
';

$temp_count = 0;
for($i=0;$i<strlen($wk_design);$i++){
	$ii = strpos($wk_design, "[RSS:",$i); 	if($ii<1) break;
	$ie = strpos($wk_design,"]]",$ii);

	$strings = substr($wk_design, $ii+5, $ie-$ii-5);
	$RSSSisterWiki = $RSSSisterWiki. "addRSS('" . $strings . "');\n"  ;
	$temp_count++;
	if($temp_count==1){
		    $wk_design = str_replace("[[RSS:".$strings."]]", "WeNeedToPlaceRSSHere",  $wk_design);
	} else {$wk_design = str_replace("[[RSS:".$strings."]]", " ",  $wk_design);}

	$i = $ii-1;
}

// javascript printing continued.. -----
	$RSSSisterWiki = $RSSSisterWiki. '

function addRSS( xml_file_name ) {

 XMLDoc = new ActiveXObject("Microsoft.XMLDOM");
XMLDoc.async = false;
XMLDoc.load( xml_file_name );
 root = XMLDoc.documentElement;
 if(root==null) { return ("Failed to read "+xml_file_name+" <BR>\n");}

 channel = root.childNodes;
 rss = channel.item(0);
 rss_title_items = rss.getElementsByTagName("title");
 rss_link_items  = rss.getElementsByTagName("link");
 rss_date_items  = rss.getElementsByTagName("pubDate");
ttools = 0;
if(rss_date_items.length<1)
{
 ttools = 1;
 rss_date_items  = rss.getElementsByTagName("dc:date");
}

if(rss_date_items.length<rss_link_items.length) offset=rss_date_items.length-rss_link_items.length; else offset=0;
for(i=(0-offset);i<rss_date_items.length;i++){
	total_domains[total_counter]    = rss_title_items.item(0).text;
	total_dates [total_counter]     = rss_date_items.item (i+offset).text;

	if(ttools==1){
		timestr = total_dates[total_counter];
		year = parseInt( timestr.substring(0, 4) );
		month= Math.abs( parseInt( ("-"+timestr.substring(5,7) ).replace("-0","") ) ) - 1;
		dayte= Math.abs( parseInt( ("-"+timestr.substring(8,10)).replace("-0","") ) );
		hour = Math.abs( parseInt( ("-"+timestr.substring(11,13)).replace("-0","")) );
		min  = Math.abs( parseInt( ("-"+timestr.substring(14,16)).replace("-0","")) );	
	total_clocks[total_counter]	= new Date( year, month, dayte, hour, min, 0 );
	//time zone not processed 
	} else { 
		total_clocks[total_counter]	= new Date( total_dates[total_counter] ); 
	}

	total_titles[total_counter]     = rss_title_items.item(i).text;
	total_link_posts[total_counter] = rss_link_items(i).text ;
	total_counter++;
}

}

// sort by posts total_clocks[i].getTime()
for(i=0;i<total_titles.length-1;i++){
	for(j=i+1;j<total_titles.length;j++){

	if( total_clocks[j].getTime() > total_clocks[i].getTime() ) {
		temp_link  = total_link_posts[i];
		temp_title = total_titles[i];
		temp_date  = total_dates[i];
		temp_clock = total_clocks[i];
		temp_domain= total_domains[i];
	
		total_link_posts[i] = total_link_posts[j];
		total_titles[i]     = total_titles[j];
		total_dates[i]      = total_dates[j];
		total_clocks[i]     = total_clocks[j];
		total_domains[i]    = total_domains[j];

		total_link_posts[j] = temp_link;
		total_titles[j]     = temp_title;
		total_dates[j]      = temp_date;
		total_clocks[j]     = temp_clock;
		total_domains[j]    = temp_domain;
	}

}
}
</script>
';




}
// RSS Sister Wiki Processing end -----------------------



		// font decoration


		// for no-smok style italic - bold decoration

                if( strpos($wk_design,"''") != false && !(strpos($wk_design,"``") != false) )
                {

$j = strlen($wk_design);
$l = 1;
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design,"''''",$i);
	if($k != false)
	{
                if ( $l == 1 ) {
                               	$forward =  substr($wk_design,0,$k);
                               	$backward = substr($wk_design,$k+4);
                               	$wk_design = $forward . " <font size=4> " . $backward;
                }
                if ( $l == -1 ) {
                               	$forward =  substr($wk_design,0,$k);
                               	$backward = substr($wk_design,$k+4);
                               	$wk_design = $forward . " </font> " . $backward;
                }

                $l = $l * (-1);
                $i = $k+4;
        }
        else
        break;
}

$j = strlen($wk_design);
$l = 1;
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design,"'''",$i);
	if($k != false)
	{
                if ( $l == 1 ) {
                               	$forward =  substr($wk_design,0,$k);
                               	$backward = substr($wk_design,$k+3);
                               	$wk_design = $forward . " <B> " . $backward;
                }
                if ( $l == -1 ) {
                               	$forward =  substr($wk_design,0,$k);
                               	$backward = substr($wk_design,$k+3);
                               	$wk_design = $forward . " </B> " . $backward;
                }

                $l = $l * (-1);
                $i = $k+3;
        }
        else
        break;
}



$j = strlen($wk_design);
$l = 1;
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design,"''",$i);
	if($k != false)
	{
                if ( $l == 1 ) {
                               	$forward =  substr($wk_design,0,$k);
                               	$backward = substr($wk_design,$k+2);
                               	$wk_design = $forward . " <I> " . $backward;
                }
                if ( $l == -1 ) {
                               	$forward =  substr($wk_design,0,$k);
                               	$backward = substr($wk_design,$k+2);
                               	$wk_design = $forward . " </I> " . $backward;
                }
                $l = $l * (-1);
                $i = $k+2;
        }
        else
        break;
}





               }

$wk_design = str_replace("==&gt; OTL &lt;==", "<center><img src='emo_otl.jpg'></center>", $wk_design);
$wk_design = str_replace("==&gt; ¾ýÁ¤´Ì &lt;==", "<center><img src='emo_ejn.jpg'></center>", $wk_design);
$wk_design = str_replace("==&gt; ", "<center><font size=6 color=red> ", $wk_design);
$wk_design = str_replace(" &lt;==", " </center></font>", $wk_design);
$wk_design = str_replace(" ````"," <font size=5>", $wk_design);
$wk_design = str_replace("'''' ","</font> ", $wk_design);
$wk_design = str_replace(" ```"," <I>",$wk_design);
$wk_design = str_replace("''' ","</I> ",$wk_design);
$wk_design = str_replace(" ``"," <B>",$wk_design);
$wk_design = str_replace("'' ","</B> ",$wk_design);
$wk_design = str_replace(" \"CR","<font color=red>",$wk_design);
$wk_design = str_replace("RC\"","</font>",$wk_design);
$wk_design = str_replace(" \"CB","<font color=blue>",$wk_design);
$wk_design = str_replace("BC\"","</font>",$wk_design);
$wk_design = str_replace(" \"CG","<font color=green>",$wk_design);
$wk_design = str_replace("GC\"","</font>",$wk_design);
$wk_design = str_replace(" \"CY","<font color=yellow>",$wk_design);
$wk_design = str_replace("YC\"","</font>",$wk_design);
$wk_design = str_replace(" \"CP","<font color=pink>",$wk_design);
$wk_design = str_replace("PC\"","</font>",$wk_design);
$wk_design = str_replace(" \"CF","<font color=purple>",$wk_design);
$wk_design = str_replace("FC\"","</font>",$wk_design);
$wk_design = str_replace("\n````"," <font size=5>", $wk_design);
$wk_design = str_replace("''''<","</font><", $wk_design);
$wk_design = str_replace("\n```"," <I>",$wk_design);
$wk_design = str_replace("'''<","</I><",$wk_design);
$wk_design = str_replace("\n``"," <B>",$wk_design);
$wk_design = str_replace("''<","</B><",$wk_design);
$wk_design = str_replace("\n\"CR","<font color=red>",$wk_design);
$wk_design = str_replace("\n\"CB","<font color=blue>",$wk_design);
$wk_design = str_replace("\n\"CG","<font color=green>",$wk_design);
$wk_design = str_replace("\n\"CY","<font color=yellow>",$wk_design);
$wk_design = str_replace("\n\"CP","<font color=pink>",$wk_design);
$wk_design = str_replace("\n\"CF","<font color=purple>",$wk_design);
$wk_design = str_replace("[[ ","<center> ",$wk_design);
$wk_design = str_replace(" ]]"," </center>",$wk_design);
		// smiley decoration
$wk_design = str_replace(" :)"," <img src=\"smile.gif\"> ",$wk_design);
$wk_design = str_replace(" B)"," <img src=\"smile2.gif\"> ",$wk_design);
$wk_design = str_replace(" :))"," <img src=\"smile3.gif\"> ",$wk_design);
$wk_design = str_replace(" ;)"," <img src=\"smile4.gif\"> ",$wk_design);
$wk_design = str_replace(" :D"," <img src=\"biggrin.gif\"> ",$wk_design);
$wk_design = str_replace(" <:("," <img src=\"frown.gif\"> ",$wk_design);
$wk_design = str_replace(" X-("," <img src=\"angry.gif\"> ",$wk_design);
$wk_design = str_replace(" :o"," <img src=\"redface.gif\"> ",$wk_design);
$wk_design = str_replace(" :("," <img src=\"sad.gif\"> ",$wk_design);
$wk_design = str_replace("{!} "," <img src=\"alert.gif\"> ",$wk_design);
$wk_design = str_replace(" {!}"," <img src=\"aelrt.gif\"> ",$wk_design);
$wk_design = str_replace("(!) "," <img src=\"idea.gif\"> ",$wk_design);
$wk_design = str_replace(" (!)"," <img src=\"idea.gif\"> ",$wk_design);
$wk_design = str_replace(" :-/"," <img src=\"ohwell.gif\"> ",$wk_design);
$wk_design = str_replace(" >:>"," <img src=\"devil.gif\"> ",$wk_design);
$wk_design = str_replace(" :\\"," <img src=\"ohwell.gif\"> ",$wk_design);
$wk_design = str_replace(" --;"," <img src=\"tired.gif\"> ",$wk_design);
$wk_design = str_replace(" - -;"," <img src=\"tired.gif\"> ",$wk_design);
$wk_design = str_replace(" ^^"," <img src=\"smile.gif\"> ",$wk_design);
$wk_design = str_replace(" ^ ^"," <img src=\"smile.gif\"> ",$wk_design);
$wk_design = str_replace(" ^__^"," <img src=\"lol.gif\"> ",$wk_design);
$wk_design = str_replace("{V}"," <img src=\"checkmark.gif\"> ",$wk_design);
$wk_design = str_replace("{ok}"," <img src=\"thumbs-up.gif\"> ",$wk_design);
$wk_design = str_replace("{±×·¯Ãé}"," <img src=\"thumbs-up.gif\"> ",$wk_design);
$wk_design = str_replace("{x}"," <img src=\"icon-error.gif\"> ",$wk_design);
$wk_design = str_replace("{i}"," <img src=\"icon-info.gif\"> ",$wk_design);
$wk_design = str_replace("\n----<BR>","</P><HR/><P>",$wk_design);

//TRICKY
$wk_design = str_replace("!?--TRICK--?!","<img src='me1.jpg' name='mine' onClick='document.mine.src=\"me2.jpg\"' onMouseOut='document.mine.src=\"me1.jpg\"' width=230 height=297>", $wk_design);


		// Hyper Link decoration
$wk_design = str_replace(" [¾Õ±Û´Þ±â]"," <a href=\"index.php?pagetoedit=".$wk_title."&edittype=reply\">´ä±Û´Þ±â</a>",$wk_design);
$wk_design = str_replace(" [´ä±Û´Þ±â]"," <a href=\"index.php?pagetoedit=".$wk_title."&edittype=reply\">´ä±Û´Þ±â</a>",$wk_design);
$wk_design = str_replace("\n[¾Õ±Û´Þ±â]","<a href=\"index.php?pagetoedit=".$wk_title."&edittype=reply\">´ä±Û´Þ±â</a>",$wk_design);
$wk_design = str_replace("\n[´ä±Û´Þ±â]","<a href=\"index.php?pagetoedit=".$wk_title."&edittype=reply\">´ä±Û´Þ±â</a>",$wk_design);

$j = strlen($wk_design); // .google tag processing
for($i=0;$i<$j;$i++){
        $k=strpos($wk_design,"[",$i);
        if($k != false)
        {
                $l = strpos($wk_design,"]",$k);
                $deco_url = substr($wk_design,$k+1,$l-$k-1);
		$deco_url = str_replace("http://","", $deco_url);
		$deco_url = str_replace("LINK:","", $deco_url);
		
		$src_prc = str_replace(" in yahoo", "", $deco_url);
		$src_prc = str_replace(" in nkino", "", $src_prc);

                if(strpos($deco_url," in nkino")!=false) {
               	$src_prc = ("http://search.nkino.com/nkino30/result.asp?keyword=".str_replace(" ","+",$src_prc)); 
		$fp=fopen($src_prc,"r");
		$src_prc = "http://img.yahoo.co.kr/globalnav/ma.gif";
		for($loop=0;$loop<3000;$loop++){
               	$line = fgets($fp);
			if(strpos($line,"http://content.nkino.com/movie/")!=false){ //"http://.co.kr/imgs")!=false){
				$img_i = strpos($line, "http://content.nkino.com/movie/"); //"http://img.srch.yahoo.co.s");
				$img_i = $img_i;
				$img_e = strpos($line, " width", $img_i); //border");
				$src_prc = substr($line, $img_i, $img_e-$img_i);
				break;
			}
			if(strpos($line, "</html>")!=false) {break;}
		}
                fclose($fp);
		
		$src_prc = "<img src=\"".$src_prc."\">";
		$wk_design = str_replace("[".$deco_url."]",$src_prc, $wk_design);
		$wk_design = str_replace("[http://".$deco_url."]",$src_prc, $wk_design);
		$wk_design = str_replace("[LINK:".$deco_url."]",$src_prc, $wk_design); 
		}

                if(strpos($deco_url," in yahoo")!=false) {
                $src_prc = ("http://kr.imagesearch.yahoo.com/search/imgbox?p=".str_replace(" ","+",$src_prc));
                $fp=fopen($src_prc,"r");
                $src_prc = "http://img.yahoo.co.kr/globalnav/ma.gif";
                for($loop=0;$loop<3000;$loop++){
                $line = fgets($fp);
                        if(strpos($line,"http://img.srch.yahoo.co.kr/imgs")!=false){
                                $img_i = strpos($line, "http://img.srch.yahoo.co.kr/imgs");
                                $img_i = $img_i;
                                $img_e = strpos($line, "border", $img_i);
                                $src_prc = substr($line, $img_i, $img_e-$img_i);
                                break;
                        }
                        if(strpos($line, "</html>")!=false) {break;}
                }
                fclose($fp);

                $src_prc = "<img src=\"".$src_prc."\">";
                $wk_design = str_replace("[".$deco_url."]",$src_prc, $wk_design);
                $wk_design = str_replace("[http://".$deco_url."]",$src_prc, $wk_design);
                $wk_design = str_replace("[LINK:".$deco_url."]",$src_prc, $wk_design);
                }

                $j = strlen($wk_design);
                $i = $l;
        } else break;
}
$j = strlen($wk_design);
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design,"[LINK:",$i);
	if($k != false)
	{
		$l = strpos($wk_design,"]",$k);
		$deco_url = substr($wk_design,$k+6,$l-$k-6);
		$deco_url_view = $deco_url;
		if(strpos($deco_url_view,":")>1 && strpos($deco_url_view,"::")<=0 && strpos($deco_url_view,"://")<=0 && strpos($deco_url_view,"ailto:")<=0 ) {
				$deco_url_view = substr($deco_url_view, strpos($deco_url_view,":")+1, 100);
				$deco_url_view = '<img src="http://no-smok.net/uploads/nomoky.png">'.$deco_url_view;
		}

		if(strpos($deco_url,".jpg")!=false) $wk_design = str_replace("[LINK:".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".Jpg")!=false) $wk_design = str_replace("[LINK:".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".JPG")!=false) $wk_design = str_replace("[LINK:".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".gif")!=false) $wk_design = str_replace("[LINK:".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".Gif")!=false) $wk_design = str_replace("[LINK:".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".GIF")!=false) $wk_design = str_replace("[LINK:".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
			$wk_design = str_replace("[LINK:".$deco_url."]","<a href=\"".$deco_url."\">".$deco_url_view."</a>",$wk_design);

                $j = strlen($wk_design);
		$i = $l;
	} else break;
}
$j = strlen($wk_design);
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design," [http://",$i);
	if($k != false)
	{
		$l = strpos($wk_design,"]",$k);
		$deco_url = substr($wk_design,$k+2,$l-$k-2);
		$deco_url_view = $deco_url;
		if(strpos($deco_url_view,":")>1 && strpos($deco_url_view,"::")<=0 && strpos($deco_url_view,"://")<=0 && strpos($deco_url_view,"ailto:")<=0 ) {
				$deco_url_view = substr($deco_url_view, strpos($deco_url_view,":")+1, 100);
				$deco_url_view = '<img src="http://no-smok.net/uploads/nomoky.png">'.$deco_url_view;
		}

		if(strlen($deco_url_view)>32) $deco_url_view = substr($deco_url_view,0,29)."...";
		
		//echo( $deco_utl );
		if(strpos($deco_url,".jpg")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".Jpg")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".JPG")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".gif")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".Gif")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".GIF")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".png")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
			$wk_design = str_replace("[".$deco_url."]","<a href=\"".$deco_url."\">".$deco_url_view."</a>",$wk_design);

                $j = strlen($wk_design);
		$i = $l;
	} else break;
}
$j = strlen($wk_design);
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design,"\n[http://",$i);
	if($k != false)
	{
		$l = strpos($wk_design,"]",$k);
		$deco_url = substr($wk_design,$k+2,$l-$k-2);
		$deco_url_view = $deco_url;
		if(strpos($deco_url_view,":")>1 && strpos($deco_url_view,"::")<=0 && strpos($deco_url_view,"://")<=0 && strpos($deco_url_view,"ailto:")<=0 ) {
				$deco_url_view = substr($deco_url_view, strpos($deco_url_view,":")+1, 100);
				$deco_url_view = '<img src="http://no-smok.net/uploads/nomoky.png">'.$deco_url_view;
		}

		if(strlen($deco_url_view)>32) $deco_url_view = substr($deco_url_view,0,29)."...";
		
		if(strpos($deco_url,".jpg")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".Jpg")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".JPG")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".gif")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".Gif")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".GIF")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
		if(strpos($deco_url,".png")!=false) $wk_design = str_replace("[".$deco_url."]","<img src=\"".$deco_url."\">",$wk_design); else
			$wk_design = str_replace("[".$deco_url."]","<a href=\"".$deco_url."\">".$deco_url_view."</a>",$wk_design);

                $j = strlen($wk_design);
		$i = $l;
	} else break;
}
$j = strlen($wk_design);
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design," [ ",$i);
	if($k != false)
	{
		$l = strpos($wk_design," ]",$k);
		$deco_url = substr($wk_design,$k+3,$l-$k-3);
		$deco_url_view = $deco_url;
		if(strpos($deco_url_view,":")>1 && strpos($deco_url_view,"::")<=0 && strpos($deco_url_view,"://")<=0 && strpos($deco_url_view,"ailto:")<=0 ) {
				$deco_url_view = substr($deco_url_view, strpos($deco_url_view,":")+1, 100);
				$deco_url_view = '<img src="http://no-smok.net/uploads/nomoky.png">'.$deco_url_view;
		}

		$wk_design = str_replace(" [ ".$deco_url." ]"," <a href=\"index.php?pagetoread=".$deco_url."\">".$deco_url_view."</a> ",$wk_design);

                $j = strlen($wk_design);
		$i = $l;
	} else break;
}
$j = strlen($wk_design);
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design,"[",$i);
	if($k != false)
	{
		$l = strpos($wk_design,"]",$k);
		$deco_url = substr($wk_design,$k+1,$l-$k-1);
		$deco_url_view = $deco_url;
		
		if(strpos($deco_url_view,":")>1 && strpos($deco_url_view,"::")<=0 && strpos($deco_url_view,"://")<=0 && strpos($deco_url_view,"ailto:")<=0 ) {
				$deco_url_view = substr($deco_url_view, strpos($deco_url_view,":")+1, 100);
				$deco_url_view = '<img src="http://no-smok.net/uploads/nomoky.png">'.$deco_url_view;
		}

		if($deco_url == str_replace(" ","",$deco_url) ) {
			$wk_design = str_replace("[".$deco_url."]"," <a href=\"index.php?pagetoread=".urlencode($deco_url)."\">".$deco_url_view."</a> ",$wk_design);
		}

                $j = strlen($wk_design);
		$i = $l;
	} else break;
}


                // Table Decoration
if( strpos($wk_design,"||<BR>\n<BR>") != false ) {

$j = strlen($wk_design);
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design,"||<BR>",$i);
	if($k != false)
	{
		if( $k == strpos($wk_design,"||<BR>\n<BR>",$i) )
		{
		// end of table
                	$forward =  substr($wk_design,0,$k);
                       	$backward = substr($wk_design,$k+2);
                      	$wk_design = $forward . " </td></tr></table> " . $backward;
                } else
                // end of row
                {
                	$forward =  substr($wk_design,0,$k);
                       	$backward = substr($wk_design,$k+6);
                      	$wk_design = $forward . " </td></tr> " . $backward;
                }

                $i = $k+1;
	} else break;
}

$l = 1;
for($i=0;$i<$j;$i++){
	$k=strpos($wk_design,"||",$i);
	if($k != false)
	{
		if( strpos($wk_design,"\n||",$i) == $k-1 ){
                  if( $l == 1 || strpos($wk_design," </td></tr></table> ", $k) != $l ) {
		        // start of table
                	$forward =  substr($wk_design,0,$k);
                       	$backward = substr($wk_design,$k+2);
                      	$wk_design = $forward . " <table border=1><tr><td> " . $backward;
                      	$l = strpos($wk_design," </td></tr></table> ",$k);
                    }
                    else {
		        // start of row
                	$forward =  substr($wk_design,0,$k);
                       	$backward = substr($wk_design,$k+2);
                      	$wk_design = $forward . " <tr><td> " . $backward;
                      	$l = strpos($wk_design," </td></tr></table> ",$l-1);
                    }
	       }
		 else
		{
			// middle of row
                	$forward =  substr($wk_design,0,$k);
                       	$backward = substr($wk_design,$k+2);
                      	$wk_design = $forward . " </td><td> " . $backward;
                      	$l = strpos($wk_design," </td></tr></table> ",$l-1);
                }

        $i = $k+1;
	} else break;
}


}

//JSoekaki extention
$j = strlen($wk_design);
for($i=0;$i<$j;$i++){
        $k=strpos($wk_design,"@JSoekaki_start@",$i);
        if($k != false)
        {
                $l = strpos($wk_design,"@JSoekaki_end@",$k);
		$picturecode = substr($wk_design, $k+16, $l-$k-16);
		$picturecode = str_replace("{","<TR HEIGHT=5>",$picturecode);
		$picturecode = str_replace("}","</TR>",$picturecode);
		$picturecode = str_replace("w","<TD BGCOLOR=WHITE HEIGHT=5 WIDTH=5></TD>",$picturecode);
                $picturecode = str_replace("b","<TD BGCOLOR=BLACK HEIGHT=5 WIDTH=5></TD>",$picturecode);
                $picturecode = str_replace("r","<TD BGCOLOR=RED HEIGHT=5 WIDTH=5></TD>",$picturecode);
                $picturecode = str_replace("y","<TD BGCOLOR=YELLOW HEIGHT=5 WIDTH=5></TD>",$picturecode);
                $picturecode = str_replace("o","<TD BGCOLOR=ORANGE HEIGHT=5 WIDTH=5></TD>",$picturecode);
                $picturecode = str_replace("s","<TD BGCOLOR=BLUE HEIGHT=5 WIDTH=5></TD>",$picturecode);
                $picturecode = str_replace("g","<TD BGCOLOR=GREEN HEIGHT=5 WIDTH=5></TD>",$picturecode);
                $picturecode = str_replace("d","<TD BGCOLOR=GRAY HEIGHT=5 WIDTH=5></TD>",$picturecode);

		$picturecode = "<table cellspacing=0 cellpadding=0>\n".$picturecode."</table>";
		$wk_design = 
		substr($wk_design,0,$k).
		$picturecode.
		substr($wk_design,$l+14);

                $j = strlen($wk_design);
                $i = $l;
        } else break;
}



// ------ Final output -------
if(file_exists("moindiff.gif")==false && strpos($wk_design,"moindiff.gif")>0 ){
	$wk_design = str_replace("<img src=\"moindiff.gif\">","<SUP><B><I><U>NEW!</U></I></B></SUP> ",$wk_design);
	}

// non decoratble processing
$wk_design = str_replace("--==NONDECO==--", "<table style='background-color: #EEEEC0;text-align:left;padding: 5px;margin: 0px 10px;'><tr style='background-color: #FFFFE0;text-align:left;padding: 5px;margin: 0px 10px;'><td style='background-color: #EEEEC0;text-align:left;padding: 5px;margin: 0px 10px;'><PRE>\n".$nondeco."</PRE></TD></TR></TABLE>", $wk_design);
if(strlen($nondeco_wk2)>2) $wk_design = $wk_design = str_replace("-==NonDeco2==-",$nondeco_wk2,$wk_design);

// RSS SisterWiki writing
if(   strpos($wk_design, "WeNeedToPlaceRSSHere")>0){
$wk_design = str_replace("WeNeedToPlaceRSSHere", $RSSSisterWiki.'
<script language="javascript">
	document.writeln("<table class=\"ierr_rss_table\">");
for(i=0;i<total_titles.length;i++)
 {
	document.writeln("<tr class=\"ierr_rss_tr\">");
	document.writeln("<td class=\"ierr_rss_td_title\">");
	document.writeln(" <a href="+total_link_posts[i]+" > ");
	document.writeln( total_titles[i] );
	document.writeln(" </a> ");
	document.writeln("</td>");

	document.writeln("<td class=\"ierr_rss_td_date\">");
	document.writeln( (total_clocks[i].getMonth()+1) + "/" + total_clocks[i].getDate() + " " + total_clocks[i].getHours()+"h" );
	document.writeln("</td>");

	document.writeln("<td class=\"ierr_rss_td_blogname\">");
	document.writeln( total_domains[i] );
	document.writeln("</td>");
	document.writeln("</tr>");
 }
	document.writeln("</table>");
	document.writeln( m_err_msg );

document.title = "("+total_clocks[0].getHours()+"h) "+total_titles[1];
document.title = document.title+", ("+total_clocks[0].getHours()+"h) "+total_titles[0];
//alert(total_titles.length);
</script>
', $wk_design);
}


//Direct catchup from ImageShack
$wk_design = str_replace("&lt;a href=\"http://img", "<a href=\"http://img", $wk_design);
$wk_design = str_replace("&gt;&lt;img src=\"http://img", "><img src=\"http://img", $wk_design);
$wk_design = str_replace("www.ImageShack.us\" /&gt;&lt;/a&gt;", "www.ImageShack.us\" /></a>", $wk_design);
$wk_design = str_replace("&lt;img src=","<img src=", $wk_design);
$wk_design = str_replace("height=300 /&gt;","height=300 />", $wk_design);
$wk_design = str_replace("height=400 /&gt;","height=400 />", $wk_design);
$wk_design = str_replace("height=288 /&gt;","height=288 />", $wk_design);

//Permitting direct link tag
$wk_design = str_replace("&lt;object width=\"425\" height=\"350\"&gt;&lt;param name=\"","<object width=\"425\" height=\"350\"><param name=\"", $wk_design);
$wk_design = str_replace("&lt;/param&gt;&lt;param name=\"","</param><param name=\"", $wk_design);
$wk_design = str_replace("&lt;/param&gt;&lt;embed src=\"","</param><embed src=\"", $wk_design);
$wk_design = str_replace("&lt;/embed&gt;&lt;/object&gt;","</embed></object>", $wk_design);
$wk_design = str_replace("&lt;a href=","<a href=", $wk_design);
$wk_design = str_replace("\"&gt;","\">",           $wk_design);
$wk_design = str_replace("&lt;/a&gt;", "</a>",     $wk_design);



// Output in fully HTMLized file
$isoption = "".($HTTP_GET_VARS["option"]);
$redirectoption = "".($HTTP_GET_VARS["redirect"]);
$wk_title = str_replace("\r","",$wk_title);
$wk_title = str_replace("\n","",$wk_title);
$first_page_file_name = ( "FIRSTPAGE.htm" );
if(strcmp($isoption,"force")==0) $m_force_dynamic_flag=1;
if((strpos($HTTP_GET_VARS["pagetoread"],"IRSTPAGE")==1 || strcmp($filename_to_read, "wkct0.txt")==0) // STATIC BASED PAGE FIRSTPAGE PROCESSING
   && $m_static_flag==1 && $m_static_auto_replace==0 && $m_force_dynamic_flag==0 && strcmp($isoption,"edited")!=0){ 
	if($m_first_run_of_wiki==0) echo "<script language='javascript'>location.replace('".$first_page_file_name."')</script><a href='".$first_page_file_name."'>FIRSTPAGE</a>";
	if($m_first_run_of_wiki==0) exit(1);
	}

if (
strpos($HTTP_GET_VARS["pagetoread"],"LIST")>2 || 
strpos($HTTP_GET_VARS["pagetoread"],"EARCH")<1 && 
(strlen($isoption)<1 || strcmp($isoption,"static")==0 || strcmp($isoption,"edited")==0 || $m_static_flag==1)
) { // create static HTM file

//	Writing file
    // PROHIBITION
$wk_design_for_static = str_replace("index.php?pagetoedit=","nonallowed.htm?p=",$wk_design);	// EDIT PROHIBITION
$wk_design_for_static = str_replace("index.php?","nonallowed.htm?",$wk_design_for_static);	// ALL THE DYNAMIC THING PROHIBITION
    // PERMISSION
$wk_design_for_static = str_replace("nonallowed.htm?pagetoread=UPDATELIST","wkct_Updated+Pages.htm"	,$wk_design_for_static); // UPDATE LIST PERMISSION
$wk_design_for_static = str_replace("nonallowed.htm?pagetoread=TITLELIST" ,"wkct_Title+List.htm"			,$wk_design_for_static); // INDEX  LIST PERMISSION
$wk_design_for_static = str_replace("nonallowed.htm?pagetoread=FIRSTPAGE","index.php",$wk_design_for_static); 								 // FIRSTPAGE PERMISSION
$d_link_I = 0; while(1==1) {									// LINK PERMISSION
		$needle = "href=\"nonallowed.htm?pagetoread=";
		$I = strpos($wk_design_for_static, $needle, $d_link_I);
		if($I<1) break;
		$E = strpos($wk_design_for_static, "\"", $I+strlen($needle));
		$keyword = substr($wk_design_for_static, $I+strlen($needle), $E-($I+strlen($needle)));
		$d_link_I = $E+1;

		if(strpos($keyword, "&")>0) continue;		if(strpos($keyword, "?")>0) continue;
		if(strpos($keyword, ">")>0) continue;		if(strpos($keyword, "<")>0) continue;

		$d_link_statement = "href=\"nonallowed.htm?pagetoread=".$keyword."\"";
		$s_link_statement = "href=\"wkct_".wikiencode($keyword).".htm\"";

		$wk_design_for_static = str_replace($d_link_statement, $s_link_statement, $wk_design_for_static);
	}
$reply_link_I =0; for($_k=0;$_k<10;$_k++){							// REPLY PERMISSION
$reply_link_I = strpos($wk_design_for_static, "&edittype=reply", $reply_link_I+1); 
	if($reply_link_I>1 && strpos(" ".$wk_title,"Title List")<=0){
		$sub_temp = substr($wk_design_for_static, 0, $reply_link_I+10);
		for($_i=0;$_i<100;$_i++){
			$link_I   = strpos($sub_temp, "nonallowed.htm?p=", $reply_link_I-$_i );
			if($link_I>0 && $link_I<$reply_link_I) break;
			}

		$wk_design_for_static=
		substr($wk_design_for_static, 0, $link_I)."index.php?pagetoedit=".substr($wk_design_for_static, $link_I+17);
		$reply_link_I = strpos($wk_design_for_static,"&edittype=reply",$link_I);
	} else {break;}
}


if(strpos($HTTP_GET_VARS["pagetoread"],"IRSTPAGE")==1 || strcmp($filename_to_read, "wkct0.txt")==0){
// FIRSTPAGE
	echo "<!-- STATIC WRITING PROCESSING -->\n";
	if(
	1==1 || 
	!(file_exists($first_page_file_name) && abs(strlen($wk_design) - filesize($first_page_file_name)) < 3)   
	)	{
		if(strpos($HTTP_GET_VARS["option"],"dited")==1 || $m_force_dynamic_flag==1){
		$fp=fopen($first_page_file_name,"wb");
		fwrite($fp, $wk_design_for_static);
		fclose($fp);}
	}
}


else {
// NONFIRSTPAGE
	echo "<!-- STATIC WRITING PROCESSING -->\n";
	if(
	1==1 || 
	!(file_exists("wkct_".wikiencode($wk_title).".htm") && abs(strlen($wk_design) - filesize( "wkct_".wikiencode($wk_title).".htm" )) < 3)
	)
		if(!(strpos($wk_design,"RecentUpdate:")>1)) {
			$fp=fopen("wkct_".wikiencode($wk_title).".htm","wb");
			fwrite($fp, $wk_design_for_static);
			fclose($fp);
		}

	if($m_static_auto_replace==1 || strcmp($redirectoption,"static")==0)
		{if($m_first_run_of_wiki==0) echo "<script language='javascript'>location.replace('wkct_".wikiencode($wk_title).".htm')</script>";}
		else
		{echo "<!--writed statically in wkct_".wikiencode($wk_title).".htm -->";}
	}
	/* static file creation end */
}

// plain output
echo $wk_design;
?>
