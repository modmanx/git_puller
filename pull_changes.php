<?php

$source = 'cmd';

$output = null;
$ret = null;
$int_return = null;

$server_name = strtolower($_SERVER['SERVER_NAME']);

$conf = include('config.php');

if(!file_exists('gitlog')){
	mkdir('gitlog');
}

$logfile = 'gitlog/git-puller-log.txt';

file_put_contents($logfile,
	'=========== ' . date('j.n.Y H:i:s') . ' ===========' . PHP_EOL .
	print_r($output, true) .
	'=== GET ===' .
	print_r($_GET, true) . 
	'=== POST ===' .
	print_r($_POST, true), FILE_APPEND);

$reps = isset($conf[$server_name]['reps']) ? $conf[$server_name]['reps'] : array();

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	$os = 'win';
} else {
	$os = 'nix';
}

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

