
<?
// ����� �Է� üũ��ƾ
function message($message)
{
 echo"
<script>
 window.alert(\"$message\");
 history.go(-1);
 </script>
";
}

if(!$name)message("�������� ������ �Է��ϼ���");
if(!$memo)message("������ ������ �Է��ϼ���");

$ipwd="0123456789ABCDEF"; 
$pwd=""; 
for($i=0;$i<6;$i++){ 
$pwd.=$ipwd[rand(0,15)]; 
} 

// ������ ��� ���� ����
if($memo)
{
      $fp=fopen("memo.txt","a");
      fputs($fp,"<font size=2 color=blue>".$email ."</font>");
      fputs($fp,"1.<font size=2 color=black>".$name ."</font>");
      fputs($fp,"2.<font size =2 color=\"#".$pwd."\">".$memo ."</font><br><hr>");
      fclose($fp);

}
// ������ �ٽ� �о�帰��.
readfile("memo.txt");

	   echo("
	         <br><br><a href=http://jangi.net/xyz/output/gongam/index.html> ���� �������� ���ư���(Ŭ��)</a>
	      ");
	      
	      
?>
