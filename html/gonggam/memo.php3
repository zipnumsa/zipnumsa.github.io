
<?
// 사용자 입력 체크루틴
function message($message)
{
 echo"
<script>
 window.alert(\"$message\");
 history.go(-1);
 </script>
";
}

if(!$name)message("송은이의 반응을 입력하세요");
if(!$memo)message("엄마의 반응을 입력하세요");

$ipwd="0123456789ABCDEF"; 
$pwd=""; 
for($i=0;$i<6;$i++){ 
$pwd.=$ipwd[rand(0,15)]; 
} 

// 파일을 열어서 쓰는 과정
if($memo)
{
      $fp=fopen("memo.txt","a");
      fputs($fp,"<font size=2 color=blue>".$email ."</font>");
      fputs($fp,"1.<font size=2 color=black>".$name ."</font>");
      fputs($fp,"2.<font size =2 color=\"#".$pwd."\">".$memo ."</font><br><hr>");
      fclose($fp);

}
// 파일을 다시 읽어드린다.
readfile("memo.txt");

	   echo("
	         <br><br><a href=http://jangi.net/xyz/output/gongam/index.html> 원래 페이지로 돌아가기(클릭)</a>
	      ");
	      
	      
?>
