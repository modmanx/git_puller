<?php

$req_id = sha1(microtime(true) . $_SERVER['REMOTE_ADDR']);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$output = null;
$ret = null;
$int_return = null;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	$os = 'win';
} else {
	$os = 'nix';
}

$server_name = strtolower($_SERVER['SERVER_NAME']);

if(file_exists('config.php')){
	$config = include('config.php');
}else{
	echo '!!! config.php does not exists !!!';
	exit;
}

$server_config = array();

if(isset($config[$server_name])){
	$server_config = $config[$server_name];
}else{	
	echo '!!! config.php does not have server config (' . $server_name . ') !!!';
	exit;
}

if(!file_exists('gitlog')){
	mkdir('gitlog');
}

$logfile = 'gitlog/git-puller-log.txt';

function send_email($params = array()){
	global $server_config;
	if(isset($server_config['mailgun'])){
		$params = array_merge(array(
			'api_url' => '',
			'api_key' => '',
			'subject' => '',
			'text' => '',
			'from' => '',
			'to' => ''
		), $server_config['mailgun'], $params);
		_send_email_mailgun($params);
	}else{

	}
}

function _send_email_mailgun($params = array()){

	$ch = curl_init();

	$params = array_merge(array(
			'api_url' => '',
			'api_key' => '',
			'subject' => '',
			'text' => '',
			'from' => '',
			'to' => ''
		), $params);

	$data = array_intersect_key(
		$params, array(
			'from' => '',
			'to' => '',
			'subject' => '',
			'text' => ''
		)
	);

	curl_setopt($ch, CURLOPT_URL, $params['api_url']);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $params['api_key']);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
	            http_build_query($data));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);

	curl_close($ch);

	return $server_output;

}

function log_write($txt, $params = array(), $extra = array()){

	global $req_id, $logfile, $server_name;
	if(isset($extra['email'])){
		send_email(array(
				'subject' => 'GitPuller - ' . $txt . ' ' . $server_name,
				'text' => date('Y-m-d H:i:s') . ' -- ' . $req_id . PHP_EOL . PHP_EOL .
							print_r($params, true) . PHP_EOL . PHP_EOL .
							print_r($params, true) . PHP_EOL . PHP_EOL .
							' ------------ ' . PHP_EOL . PHP_EOL	.
							print_r($_POST, true) . PHP_EOL . PHP_EOL .
							print_r($_GET, true)
			));
	}
	file_put_contents($logfile, '[' . date('Y-m-d H:i:s') . '] ' .
		$txt . ' --- ' . json_encode($params) . ' --- ' . $req_id, FILE_APPEND);

}

if(isset($_GET['test'])){
	echo 'server name = ' . $server_name . '<br />';
	echo 'OS = ' . $os . '<br />';
	echo '<hr />';
	echo 'testing email:<br />';
	// make email test
	echo '<hr />';
	echo 'testing if log folder is writable:<br />';
	if(!is_writable('gitlog')){
		echo '!!! gitlog folder is not writable !!!';
	}else{
		echo 'folder is writable';
	}
	echo 'testing if config.php exists:<br />';
	if(!file_exists('config.php')){
		echo '!!! config.php DOES NOT exists !!!';
		exit;
	}else{
		echo 'config.php exists';
	}
	echo '<hr />';
	$conf = include('config.php');
	echo 'testing if config.php include config for this server:<br />';
	if(!isset($conf[$server_name])){
		echo '!!! config.php DOES NOT include config for this server !!!';
	}else{
		echo 'config.php does include config for this server';
	}
	echo '<hr />';
	echo 'testing if config.php has OK structure:<br />';
	$struct_err = false;
	if(!isset($conf[$server_name])){
		echo 'conf for ' . $server_name . ' DOES NOT exists ';
		exit;
	}
	$sconf = $conf[$server_name];
	if(!isset($sconf['reps'])){
		echo 'conf doesnt have \'reps\' key';
		exit;
	}
	foreach($sconf['reps'] as $repurl => $repconf){
		if($repurl !== $repconf['url']){
			echo 'conf for ' . $repurl . ' has different key url - ' . $repconf['url'];
			exit;
		}
		if($repurl[strlen($repurl) - 1] == '/'){
			echo '!!! ' . $repurl . ' has / at end. Please remove or report bug. !!!';
			exit;
		}
		foreach($repconf['branches'] as $branch_name => $branch){
			foreach($branch['folders'] as $folder){
				if(!file_exists($folder['path'])){
					echo 'folder ' . $folder['path'] . ' DOES NOT exists ';
					exit;
				}
				/*if(!file_exists($folder['path']) . '.git'){
					echo 'folder ' . $folder['path'] . '.git DOES NOT exists ';
					exit;
				}*/
			}
		}
	}
	if(!$struct_err){
		echo 'config.php is OK';
	}
	echo '<hr />';
	echo 'testing if for env vars:<br />';
	if($os == 'win'){
		exec('echo %GIT_BIN_PATH%', $output);
		if($output[0] == '%GIT_BIN_PATH%'){
			echo '!!! GIT_BIN_PATH var not set. Set it to something like c:\\Program Files (x86)\\Git\\bin\\ !!!<br />';
		}else{
			echo '%GIT_BIN_PATH% is OK<br />';
		}
	}
	echo '<hr />';
	if($os == 'nix'){
		echo 'testing if git-puller-' . $os . '.sh is executable:<br />';
		if(!file_exists('./git-puller-' . $os . '.sh')){
			echo '!!! git-puller-' . $os . '.sh DOES NOT exists !!!<br />';
			exit;
		}else{
			echo 'git-puller-' . $os . '.sh exists<br />';
		}
		if(!is_executable('./git-puller-' . $os . '.sh')){
			echo '!!! git-puller-' . $os . '.sh IS NOT executable !!!<br />';
			exit;
		}else{
			echo 'git-puller-' . $os . '.sh IS executable<br />';
		}
		echo '<hr />';		
	}
	exit;	
}

$conf = include('config.php');

log_write('request', array('post' => $_POST));

$reps = isset($server_config['reps']) ? $server_config['reps'] : array();

if(isset($_POST['payload'])){

	try{
		$json = json_decode($_POST['payload'], true);
	}catch(Exception $e){
		log_write('payload broken', array(), array('email' => true));
		exit;
	}
	log_write('payload', $json);
	if(!isset($json['repository'])){
		log_write('payload repository broken', array(), array('email' => true));
		exit;
	}
	
	$ref = explode('/', $json['ref']);	
	$branch = $ref[count($ref) - 1];
	log_write('branch: ' . $branch);
	
	if(isset($reps[$json['repository']['url']])){
		$repo = $reps[$json['repository']['url']];
	}else{
		log_write('repo does not exists: ' . $json['repository']['url'], array('reps' => $reps), array('email' => true));
		exit;
	}
	
	if(isset($repo['branches'][$branch])){
		$branch_data = $repo['branches'][$branch];
	}else{
		log_write('branch does not exists: ' . $branch, array('repo' => $repo), array('email' => true));
		exit;
	}
	
	foreach($branch_data['folders'] as $folder){
		log_write('calling git-puller.sh');
		if($os == 'win'){
			exec('"%GIT_BIN_PATH%/sh.exe" ./git-puller-' . $os . '.sh ' .
				'"' . $folder['path'] . '" ' . $branch . ' 2>&1',
				$output, $int_return);			
		}else{
			exec('./git-puller-' . $os . '.sh ' .
				'"' . $folder['path'] . '" ' . $branch . ' 2>&1',
				$output, $int_return);	
		}
		log_write('output', $output, array('email' => true));
	}
	
}else if(isset($_GET['do_pull'])){

	if(!isset($_GET['ref'])){
		log_write('GET ref not set', array(), array('email' => true));
		exit;
	}
	
	if(!isset($_GET['url'])){
		log_write('GET url not set', array(), array('email' => true));
		exit;
	}

	$ref = explode('/', $_GET['ref']);	
	$branch = $ref[count($ref) - 1];
	log_write('branch: ' . $branch);
	
	if(isset($reps[$_GET['url']])){
		$repo = $reps[$_GET['url']];
	}else{
		log_write('repo does not exists: ' . $_GET['url'], array(), array('email' => true));
		exit;
	}
	
	if(isset($repo['branches'][$branch])){
		$branch_data = $repo['branches'][$branch];
	}else{
		log_write('branch does not exists: ' . $branch, array(), array('email' => true));
		exit;
	}
	
	foreach($branch_data['folders'] as $folder){
		log_write('calling git-puller.sh');
		if($os == 'win'){
			exec('"%GIT_BIN_PATH%/sh.exe" ./git-puller-' . $os . '.sh ' .
				'"' . $folder['path'] . '" ' . $branch . ' 2>&1',
				$output, $int_return);			
		}else{
			exec('./git-puller-' . $os . '.sh ' .
				'"' . $folder['path'] . '" ' . $branch . ' 2>&1',
				$output, $int_return);	
		}
		log_write('output', $output, array('email' => true));
		echo 'output<pre>' . print_r($output, true) . '</pre>';
	}

}

