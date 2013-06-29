<?php

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

if(!file_exists('gitlog')){
	mkdir('gitlog');
}

$logfile = 'gitlog/git-puller-log.txt';

if(isset($_GET['test'])){
	echo 'server name = ' . $server_name . '<br />';
	echo 'OS = ' . $os . '<br />';
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

file_put_contents($logfile,
	'=========== ' . date('j.n.Y H:i:s') . ' ===========' . PHP_EOL .
	print_r($output, true) .
	'=== GET ===' .
	print_r($_GET, true) . 
	'=== POST ===' .
	print_r($_POST, true), FILE_APPEND);

$reps = isset($conf[$server_name]['reps']) ? $conf[$server_name]['reps'] : array();

if(isset($_POST['payload'])){

	try{
		$json = json_decode($_POST['payload'], true);
	}catch(Exception $e){
		file_put_contents($logfile, 'payload broken' . PHP_EOL, FILE_APPEND);
		exit;
	}
	file_put_contents($logfile, 'payload: ' . print_r($json, true) . PHP_EOL, FILE_APPEND);
	if(!isset($json['repository'])){
		file_put_contents('git-puller-log.txt', 'payload repository broken' . PHP_EOL, FILE_APPEND);
		exit;
	}
	
	$ref = explode('/', $json['ref']);	
	$branch = $ref[count($ref) - 1];
	file_put_contents($logfile, '-> branch: ' . $branch . PHP_EOL, FILE_APPEND);
	
	if(isset($reps[$json['repository']['url']])){
		$repo = $reps[$json['repository']['url']];
	}else{
		file_put_contents($logfile, '-> repo does not exists: ' . $json['repository']['url'] . PHP_EOL, FILE_APPEND);
		exit;
	}
	
	if(isset($repo['branches'][$branch])){
		$branch_data = $repo['branches'][$branch];
	}else{
		file_put_contents($logfile, '-> branch does not exists: ' . $branch . PHP_EOL, FILE_APPEND);
		exit;
	}
	
	foreach($branch_data['folders'] as $folder){
		file_put_contents($logfile, '-> calling git-puller.sh' . PHP_EOL, FILE_APPEND);
		if($os == 'win'){
			exec('"%GIT_BIN_PATH%/sh.exe" ./git-puller-' . $os . '.sh ' .
				'"' . $folder['path'] . '" ' . $branch_name . ' 2>&1',
				$output, $int_return);			
		}else{
			exec('./git-puller-' . $os . '.sh ' .
				'"' . $folder['path'] . '" ' . $branch_name . ' 2>&1',
				$output, $int_return);	
		}
		file_put_contents($logfile, '-> output: ' . PHP_EOL . print_r($output, true) . PHP_EOL, FILE_APPEND);
	}
	
}else if(!isset($_GET['do_pull'])){
	echo 'do_pull NOT SET';
	file_put_contents($logfile,
                '=========== ' . date('j.n.Y H:i:s') . ' =========== do_pull NOT SET' . PHP_EOL .
                print_r($output, true) .
                '=== GET ===' .
                print_r($_GET, true) .
                '=== POST ===' .
                print_r($_POST, true), FILE_APPEND);
        exit;
}

