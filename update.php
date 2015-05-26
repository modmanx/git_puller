<?php

// copy this file outside of this dir.

$path_deploy = dirname(__FILE__) . '/deploy/';
$path_tmp = $path_deploy . '/' . 'tmp';
$path_tmp_install = $path_tmp . '/install';

// check if deploy folder exists
if(!file_exists($path_deploy)){
  mkdir($path_deploy);
  mkdir($path_tmp);
  mkdir($path_tmp_install);
}
echo '<pre>';

if(file_exists($path_tmp_instal)){
  // unlink($path_tmp_install);
  echo 'removin previous install dir';
  echo exec('rm -rf ' . $path_tmp_install);
  mkdir($path_tmp_install);
}

$path_install_zip = $path_tmp . 'install.zip';

echo 'folders ' . $path_deploy . ' and ' . $path_tmp . ' exists' . PHP_EOL;

file_put_contents($path_install_zip, file_get_contents('https://github.com/modmanx/git_puller/archive/master.zip'));

$zip = new ZipArchive;
$res = $zip->open($path_install_zip);
if ($res === TRUE) {
  $zip->extractTo($path_deploy);
  $zip->close();
  echo 'Zip extracted';
} else {
  echo 'extraction error';
  exit;
}

echo exec('mv ' . $path_deploy . 'git_puller-master/* ' . $path_deploy);

if(!file_exists($path_deploy . 'config.php')){
  copy($path_deploy . 'config.sample.php', $path_deploy . 'config.php');
}

if(file_exists($path_tmp_instal)){
  // unlink($path_tmp_install);
  echo 'removin previous install dir';
  echo exec('rm -rf ' . $path_tmp_install);
  mkdir($path_tmp_install);
}

echo PHP_EOL;
