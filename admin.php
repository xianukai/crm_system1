<?php
error_reporting(E_ALL & ~E_NOTICE);

require_once "user_auth.php";

#
# 基本設定
#

# 管理パスワード(1234)
$admin_id = "yamada";
$md5_pw = "81dc9bdb52d04dc20036dbd8313ed055";


# データベースに接続
$testuser = "〇〇〇〇";
$testpass = "m〇〇〇〇〇〇";
$host = "mysql738.db.sakura.ne.jp";
$datebase = "qlpt_crm";

# テンプレートディレクトリ
$tmpl_dir = "./tmpl";

#
# ページの表示
#
parse_form();
user_auth();

if($in["mode"] == "login" || $in["mode"] == ""){ login(); }
else if($in["mode"] == "logout"){ logout(); }
else if(isset($_SESSION["input_id"]) && isset($_SESSION["input_pw"])){
	try {
		$db = new PDO("mysql:host={$host}; dbname={$datebase}; charset=utf8", $testuser, $testpass);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if($in["state"] == "insert") { item_insert(); }
			else if($in["state"] == "update") { item_update(); }
			else if($in["state"] == "delete") { item_delete(); }
			item_search();
	}catch (PDOException $e) {
		die("PDO Error:" .$e->getMessage());
	}
}
else{
	error("不正な処理です");
}

#
# フォーム受け取り
#
function parse_form(){
	global $in;

	$param = array();
	if (isset($_GET) && is_array($_GET)) { $param += $_GET; }
	if (isset($_POST) && is_array($_POST)) { $param += $_POST; }
	
	foreach($param as $key => $val) {
		# 2次元配列から値を取り出す
		if(is_array($val)){
			$val = array_shift($val);
		}
		
		# 文字コードの処理
		$enc = mb_detect_encoding($val);
		$val = mb_convert_encoding($val,"UTF-8",$enc);
		
		# 特殊文字の処理
		$val = htmlentities($val,ENT_QUOTES, "UTF-8");

		$in[$key] = $val;
	}
	return $in;
}

#
# ログイン画面
#
function login() {
	# テンプレ読み込み
	$tmpl = page_read("login");
	echo $tmpl;
	exit;
}

#
# ログアウト画面
#
function logout() {
	session_destroy();
	
	# テンプレート読み込み
	$tmpl = page_read("logout");
	echo $tmpl;
	exit;
}

#-----------------------------------------------------------
# 商品登録
#-----------------------------------------------------------
function item_insert(){
	global $in;
	global $db;
	global $tmpl_dir;

	#エラーチェック
	$error_notes="";
	if($in["name"] == ""){
		$error_notes.="・登録IDが未入力です。<br>";
	}
	/*
	if($in["gender"] == ""){
		$error_notes.="・名前が未入力です。<br>";
	}*/
	
	#エラーが存在する場合
	if($error_notes != "") {
		error($error_notes);
	}
	
	# プリペアードステートメントを準備
	$stmt = $db->prepare('INSERT INTO item(name, kana, gender, postal, postal2, address,tel,item_flag) VALUES(:name, :kana, :gender, :postal, :postal2, :address, :tel, 1)');
	
	# 変数を束縛する
	$stmt->bindParam(':name', $name);
	$stmt->bindParam(':kana', $kana);
	$stmt->bindParam(':gender', $gender);
	$stmt->bindParam(':postal', $postal);
	$stmt->bindParam(':postal2', $postal2);
	$stmt->bindParam(':address', $address);
	$stmt->bindParam(':tel', $tel);
	
	# 変数に値を設置し、SQLを実行
	$name = $in["name"];
	$kana = $in["kana"];
	$gender = $in["gender"];
	$postal = $in["postal"];
	$postal2 = $in["postal2"];
	$address = $in["address"];
	$tel = $in["tel"];
	$stmt->execute();
}

#-----------------------------------------------------------
# 商品編集
#-----------------------------------------------------------
function item_update(){
	global $in;
	global $db;
	global $tmpl_dir;
	
	#エラーチェック
	/*
	$error_notes="";
	if($in["user_id"] == ""){
		$error_notes.="・user_idを選択してください。<br>";
	}
	#エラーが存在する場合
	if($in["item_id"] == ""){
		$error_notes.="・編集する商品を選択してください。<br>";
	}
	*/
	$error_notes="";
	if($in["name"] == ""){
		$error_notes.="・スタッフ名が未入力です。<br>";
	}
	/*
	if($in["address"] == "") {
		$error_notes .= "・住所が未入力です。<br>";
	}
	if($in["gender"] == ""){
		$error_notes.="・性別が未入力です。<br>";
	}
	*/
	
	#エラーが存在する場合
	if($error_notes != "") {
		error($error_notes);
	}
	
	# プリペアードステートメントを準備
	$stmt = $db->prepare('UPDATE item SET name = :name, kana = :kana, gender = :gender, postal = :postal, postal2 = :postal2, address = :address,tel =	:tel WHERE user_id = :user_id');
	
	# 変数を束縛する //bindParamと$変数の数は一致しなきゃいけない
	$stmt->bindParam(':user_id', $user_id);
	$stmt->bindParam(':name', $name);
	$stmt->bindParam(':kana', $kana);
	$stmt->bindParam(':gender', $gender);
	$stmt->bindParam(':postal', $postal);
	$stmt->bindParam(':postal2', $postal2);
	$stmt->bindParam(':address', $address);
	$stmt->bindParam(':tel', $tel);
	
	# 変数に値を設定し、SQLを実行
	$user_id = $in["user_id"];
	$name = $in["name"];
	$kana = $in["kana"];
	$gender = $in["gender"];
	$postal = $in["postal"];
	$postal2 = $in["postal2"];
	$address = $in["address"];
	$tel = $in["tel"];
	$stmt->execute();
}

#
# 商品削除
#
function item_delete() {
	global $in;
	global $db;
	global $tmpl_dir;
	
	# error check
	$error_notes="";
	if($in["user_id"] == "") {
		$error_notes .= "・削除する商品を選択してください。<br>";
	}
	
	# エラーが存在する場合
	if($error_notes != "") {
		error($error_notes);
	}
	
	# プリペアードステートメントを準備
	$stmt = $db->prepare('UPDATE item SET item_flag = 0 WHERE user_id = :user_id');
	
	# 変数を束縛する
	$stmt->bindParam(':user_id', $user_id);
	
	# 変数に値を設定し、SQLを実行
	$user_id = $in["user_id"];
	$stmt->execute();
}

#
# 商品一覧(メンバー人材一覧) //管理トップ画面
#
function item_search() {
	global $in;
	global $db;
	global $tmpl_dir;
	
	# 自身のパス
	$script_name=$_SERVER['SCRIPT_NAME'];
	
	# SQLを作成
	$query = "SELECT * FROM item WHERE item_flag = 1"; //ここで削除されてitem_flag = 0;の場合は表示されなくなっちゃう？
	
	# プリペアードステートメントを準備
	$stmt = $db->prepare($query);
	$stmt->execute();
	
	$item_data = "";	
	while($row = $stmt->fetch()){
		$user_id = $row['user_id'];
		$item_data .= "<tr>";
		$item_data .= "<td class=\"number\">$user_id</td>";
		$item_data .= "<td class=\"name\">$row[name]<span>$row[kana]</span></td>";
		$item_data .= "<td class=\"gender\">$row[gender]</td>";
		$item_data .= "<td class=\"address\"><span>〒&nbsp;$row[postal]-$row[postal2]</span>$row[address]</td>";
		$item_data .= "<td class=\"phone\">$row[tel]</td>";
		$item_data .= "<td class=\"setting1-1\">$row[setting1]</td>";
		$item_data .= "<td class=\"original1-2\">$row[setting2]</td>";
		$item_data .= "<td class=\"original1-3\">$row[setting3]</td>";
		$item_data .= "<td><a href=\"$script_name?mode=item&user_id=$user_id\">編集</a><a href=\"$script_name?mode=item&state=delete&user_id=$user_id\">削除</a></td>";

		$item_data .= "</tr>\n";
		/*
		$item_data .= "<td><a href=\"$script_name?mode=item&item_id=$item_id\">編集</a>
		</td>";
		$item_data .= "<td><a href=\"$script_name?mode=item&state=delete&item_id=$item_id\">削除</a></td>";
		$item_data .= "</tr>\n";
		*/
	}
	
  if($in["user_id"] != "") {
		# 選択した商品IDに対応する情報を取得
		$stmt = $db->prepare('SELECT * FROM item WHERE user_id = :user_id');
		$stmt->bindParam(':user_id', $user_id);
		$user_id = $in["user_id"];
		$stmt->execute();
		$row = $stmt->fetch();
		$name = $row["name"];
		$kana = $row["kana"];
		$gender = $row["gender"];
		$postal = $row["postal"];
		$postal2 = $row["postal2"];
		$address = $row["address"];
		$tel = $row["tel"];
		$setting1 = $row["setting1"];
		$setting2 = $row["setting2"];
		$setting3 = $row["setting3"];
		
		# 掲示板テンプレート読み込み
		$tmpl = page_read("item_edit");
		# 文字変換
		$tmpl = str_replace("!user_id!", $in["user_id"], $tmpl);
		$tmpl = str_replace("!name!", $name, $tmpl);
		$tmpl = str_replace("!kana!", $kana, $tmpl);
		$tmpl = str_replace("!gender!", $gender, $tmpl);
		$tmpl = str_replace("!postal!", $postal, $tmpl);
		$tmpl = str_replace("!postal2!", $postal2, $tmpl);
		$tmpl = str_replace("!address!", $address ,$tmpl);
		$tmpl = str_replace("!tel!", $tel ,$tmpl);
		$tmpl = str_replace("!setting1!", $setting1, $tmpl);
		$tmpl = str_replace("!setting2!", $setting2, $tmpl);
		$tmpl = str_replace("!setting3!", $setting3, $tmpl);
		$tmpl = str_replace("!item_data!", $item_data, $tmpl);
	}
	else {
		# 掲示板テンプレート読み込み
		$tmpl = page_read("item"); //ここをitem_editやerrorにしたら、表示がitemから切り替わる（当然だけど）
		# 文字変換
		$tmpl = str_replace("!item_data!", $item_data, $tmpl);
	}
	echo $tmpl;
	exit;
}



#
# エラー画面
#
function error($errmes) {
	global $tmpl_dir;
	$msg = $errmes;
	
	# エラーテンプレート読み込み
	$tmpl = page_read("error");
	
	# 文字置き換え
	$tmpl = str_replace("!message!", "$msg", $tmpl);
	echo $tmpl;
	exit;
}

#
# ページ読み取り
#
function page_read($page) {
	global $tmpl_dir;
	
	# テンプレート読み込み
	$conf = fopen( "$tmpl_dir/{$page}.tmpl", "r") or die;
	$size = filesize("$tmpl_dir/{$page}.tmpl");
	$tmpl = fread($conf, $size);
	fclose($conf);
	
	return $tmpl;
}
?>

<?php

ini_set( 'display_errors' , 0 ); //エラー表示しない
error_reporting(E_ALL & ~E_DEPRECATED); //互換性注意以外の全エラーを扱う
ini_set('log_errors' , 1); //エラーログをファイルに保存する
ini_set('error_log', 'C:\xampp\apache\logs\php_error.log'); //エラーログを保存するファイルの指定

echo $aaa;

# ディレクトリ・トラバーサル対策
function get_filename() {
  $filename = $_POST['filename'];
  if (strpos($filename, '..') !== false) {
    exit('不正なアクセスです。');
  }
  return str_replace('\0','' , $filename);
}

$filename = get_filename();
$file = 'www/html/'. $filename;
if (file_exists($file) === true) {
  readfile($file);
}

?>
