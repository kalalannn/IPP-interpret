<?php
define('OK', 0);
define('PAR_ERROR', 10);

$directory = './';
$parse_script = './parse.php';
$int_script = './interpret.php';
$recursive = false;
$int_only = false;
$parse_only = false;
$php = 'php7.3';
$prog_name = $argv[0];
$tests = '<hr>';
$number_of_test = 1;
$coding = '"utf-8"';

foreach ($argv as $arg) {
	if ($arg == "--help") {
		print_help();
		exit(OK);
	} elseif ($arg == '--recursive') {
		$recursive = true;
	} elseif ($arg == '--parse-only') {
		$parse_only = true;
	} elseif ($arg == '--int-only') {
		$int_only = true;
	} elseif (preg_match("/^--directory=.*$/", $arg)) {
		$dir = explode('=', $arg);
		$directory = $dir[1];
		if (!(preg_match('/^.*\/$/', $directory))) {
			$directory = $directory.'/';
		}
	} elseif (preg_match("/^--parse-script=.*$/", $arg)) {
		$file= explode('=', $arg);
		$parse_script= $file[1];
	} elseif (preg_match("/^--int-script=.*$/", $arg)) {
		$file = explode('=', $arg);
		$int_script = $file[1];
	} elseif ($arg == $prog_name) {
		continue;
	} else {
		echo "ParError: Wrong argument\n";
		exit(PAR_ERROR);
	}
}

if ($parse_only == $int_only && $parse_only == true) {
	echo "ParError: Can not use --int-only and --parse-only together\n";
	exit(PAR_ERROR);
}

__main__($directory);


echo (
	"<!doctype html>\n".
	"<html>\n".
	"  <head>\n".
	"    <meta charset=$coding>\n".
	"      <title>test.php output</title>\n".
	"  </head>\n".
	"  <body>\n".
	"    <h1>Tests result</h1>\n".
	$tests."\n".
	"  </body>\n".
	"</html>\n");

function print_help() {
	echo (
		"****|===================================================|****\n".
		"    |      Help page for IPPcode19 tester part          |\n".
		"    |===================================================|\n".
		"    |                                                   |\n".
		"    |Version: 1.0                                       |\n".
		"    |Autor: Nikolaj Vorobiev                            |\n".
		"    |Email: xvorob00@stud.fit.vutbr.cz                  |\n".
		"    |                                                   |\n".
		"    |Usage:                                             |\n".
		"    |     php7.3 test.php {parameters}                  |\n".
		"    |     parameters:                                   |\n".
		"    |       --help                                      |\n".
		"    |       --directory=path                            |\n".
		"    |       --recursive                                 |\n".
		"    |       --parse-script=file                         |\n".
		"    |       --int-script=file                           |\n".
		"    |       --parse-only                                |\n".
		"    |       --int-only                                  |\n".
		"****|===================================================|****\n".
		""
	);
}


function generate_test_html($path, $parser_src, $parser_out, $test_out, $parser_rc, $test_rc, $test_in) {
	global $tests, $number_of_test;
	$lang = '"xml"';
	$tests = $tests.
	"    <h2>Test $number_of_test</h2>\n".
	"    <h3>$path.src</h3>\n".
	"    <pre>".$parser_src."</pre>\n".
	"    <h3>Parser output</h3>\n".
	"    <pre lang=$lang>".$parser_out."</pre>\n".
	"    <h3>$path.out</h3>\n".
	"    <pre lang=$lang>".$test_out."</pre>\n".
	"    <h3>Parser return code</h3>\n".
	"    <pre>$parser_rc</pre>\n".
	"    <h3>$path.rc</h3>\n".
	"    <pre>$test_rc</pre>\n".
	"    <h3>$path.in</h3>\n".
	"    <pre>$test_in</pre>\n".
	"    <hr>\n";

	$number_of_test++;
}

function make_test($file_name, $directory) {
	global $parse_script, $int_script, $recursive, $int_only, $parse_only, $php, $tests;
	$path = $directory.$file_name;
	$command = $php.' '.$parse_script.' '.'<'.' '.$path.'.src';
	system($command.' >'.'temp.parser.out', $parser_rc);
	$parser_src = file_get_contents($path.'.src');
	$parser_out = file_get_contents('temp.parser.out');
	system('rm temp.parser.out');
	$test_out = file_get_contents($path.'.out');
	$test_in = file_get_contents($path.'.in');
	$test_rc = file_get_contents($path.'.rc');
	generate_test_html($path, htmlentities($parser_src), htmlentities($parser_out), htmlentities($test_out), $parser_rc, $test_rc, $test_in);
}

function check_files($file_name, $directory) {
	foreach (array('.in', '.out', '.rc') as $file_ext) {
		if (is_file($directory.$file_name.$file_ext)) {
			continue;
		} else {
			//echo "Action: touch $directory.$file_name.$file_ext\n";
			touch($directory.$file_name.$file_ext);
			if($file_ext == '.rc') {
				//echo "Action: put 0 to $directory.$file_name.$file_ext\n";
				file_put_contents($directory.$file_name.$file_ext, '0');
			}
		}
	}
}

function __main__($directory) {
	global $recursive;
	$file_array = array();
	//echo $directory."\n";
	foreach (scandir($directory) as $file) {
		if(preg_match('/^.*.src$/', $directory.$file)) {
			$file_name = explode('.',  $file);
			$file_name = $file_name[0];
			check_files($file_name, $directory);
			make_test($file_name, $directory);
		} elseif ($file == '.' || $file == '..') {
			continue;
		} elseif (is_dir($directory.$file) && $recursive == true) {
			__main__($directory.$file.'/');
		}
	}
}

?>
