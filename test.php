<?php

/* !
 * @author  Nikolaj Vorobiev
 * @license GNU GPL3
 */

define('OK', 0);
define('PAR_ERROR', 10);
define('GREEN', '"color:#008000"');
define('RED', '"color:#FF0000"');
define('ORANGE', '"color:#FFA500"');

# defaults
$directory = './';
$parse_script = './parse.php';
$int_script = './interpret.py';
$recursive = false;
$int_only = false;
$parse_only = false;
$prog_name = $argv[0];
$number_of_test = 1;
$coding = '"utf-8"';
$red_tests = 0;
$orange_tests = 0;
$green_tests = 0;
$tests = '';
$err_tests = '';
$red = RED;
$green = GREEN;
$orange = ORANGE;

# Arguments
foreach (array_slice($argv, 1) as $arg) {
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
		if (is_dir($directory)) {
			$directory = $dir[1];
		}
		if (!(preg_match('/^.*\/$/', $directory))) {
			$directory = $directory.'/';
		}
	} elseif (preg_match("/^--parse-script=.*$/", $arg)) {
		$file= explode('=', $arg);
		$parse_script= $file[1];
	} elseif (preg_match("/^--int-script=.*$/", $arg)) {
		$file = explode('=', $arg);
		$int_script = $file[1];
	} else {
		echo "ParError: Wrong argument\n";
		exit(PAR_ERROR);
	}
}

if ($parse_only == $int_only && $parse_only == true) {
	echo "ParError: Can not use --int-only and --parse-only together\n";
	exit(PAR_ERROR);
}

test($directory);

$pars = '';
foreach (array_slice($argv, 1) as $par) {
	$pars = $pars."<p>".$par."</p>"."\n";
}

echo (
	"<!doctype html>\n".
	"<html>\n".
	"  <head>\n".
	"    <meta charset=$coding>\n".
	"      <title>test.php output</title>\n".
	"  </head>\n".
	"  <body>\n".
	"    <h1>Tests results</h1>\n".
	"    <p style=$red>red_tests: $red_tests</p>\n".
	"    <p style=$green>green_tests: $green_tests</p>\n".
	"    <p style=$orange>orange_tests: $orange_tests</p>\n".
	"    <h3>Params: </h3>\n".
	$pars."\n".
	"<hr>\n".
	$err_tests."\n".
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

/*! 
 *
 * @brief Generate html from test results
 */
function generate_test_html($path, $src, $parser_out, $parser_rc, $int_out, $int_rc, $test_out, $test_rc, $test_in) {
	global $tests, $err_tests, $number_of_test;
	$lang = '"xml"';

	if (is_null($int_rc)) { 			# parse-only
		$output = ''.
			"    <h3>Parser output</h3>\n".
			"    <pre lang=$lang>".$parser_out."</pre>\n".
			"    <h3>Test output</h3>\n".
			"    <pre lang=$lang>".$test_out."</pre>\n".
			"    <h3>Parser rc</h3>\n".
			"    <pre>$parser_rc</pre>\n".
			"    <h3>Test rc</h3>\n".
			"    <pre>$test_rc</pre>\n";
		if ($test_rc == $parser_rc) {
			$color = GREEN; # green
		} else {
			$color = RED;  # red
		}

	} elseif (is_null($parser_rc)) { 	# int_only
		$output = ''.
			"    <h3>Interpret output</h3>\n".
			"    <pre lang=$lang>".$int_out."</pre>\n".
			"    <h3>Test in</h3>\n".
			"    <pre>$test_in</pre>\n".
			"    <h3>Test output</h3>\n".
			"    <pre lang=$lang>".$test_out."</pre>\n".
			"    <h3>Interpret rc</h3>\n".
			"    <pre>$int_rc</pre>\n".
			"    <h3>Test rc</h3>\n".
			"    <pre>$test_rc</pre>\n";
		if ($test_rc == $int_rc) {
			if ($test_out == $int_out) { # my err string
				$color = GREEN; # green
			} elseif ($int_rc == 0) {
				$color = RED; # red
			} else {
				$color = ORANGE; # orange
			}
		} else {
			$color = RED; # red
		}
	} else {
		$output = ''.
			"    <h3>Parser output</h3>\n".
			"    <pre lang=$lang>".$parser_out."</pre>\n".
			"    <h3>Parser rc</h3>\n".
			"    <pre>$parser_rc</pre>\n".
			"    <h3>Test in</h3>\n".
			"    <pre>$test_in</pre>\n".
			"    <h3>Interpret output</h3>\n".
			"    <pre lang=$lang>".$int_out."</pre>\n".
			"    <h3>Test output</h3>\n".
			"    <pre lang=$lang>".$test_out."</pre>\n".
			"    <h3>Interpret rc</h3>\n".
			"    <pre>$int_rc</pre>\n".
			"    <h3>Test rc</h3>\n".
			"    <pre>$test_rc</pre>\n";
		if ($test_rc == $int_rc) {
			if ($test_out == $int_out) { # my err string
				$color = GREEN; # green
			} elseif ($int_rc == 0) {
				$color = RED; # red
			} else {
				$color = ORANGE; # orange
			}
		} else {
			$color = RED; # red
		}
	}
	$head = ''.
		"    <h2 style=$color>Test $number_of_test</h2>\n".
		"    <h3>$path.src</h3>\n".
		"    <pre>".$src."</pre>\n";

	global $red_tests, $orange_tests, $green_tests;
	if ($color == RED) { # red
		$err_tests = $err_tests.$head.$output."<hr>\n";
		$red_tests++;
	} elseif ($color == ORANGE) { # orange
		$tests = $tests.$head.$output."<hr>\n";
		$orange_tests++;
	} elseif ($color == GREEN) { # green
		$tests = $tests.$head.$output."<hr>\n";
		$green_tests++;
	}
	$number_of_test++;
}

/*!
 * Make test
 * Diff Parser/Interpret out with test out
 * Diff Parser/Interpret rc  with test rc
 */
function make_test($file_name, $directory) {
	global $parse_script, $int_script, $recursive, $int_only, $parse_only;
	$path = $directory.$file_name;

	$src = file_get_contents($path.'.src');
	$test_out = file_get_contents($path.'.out');
	$test_in = file_get_contents($path.'.in');
	$test_rc = file_get_contents($path.'.rc');

	$parser_out = NULL;
	$parser_rc = NULL;
	$int_out = NULL;
	$int_rc = NULL;

	$parse = 'php7.3 '.$parse_script.' '.'<'.' '.$path.'.src';
	$int = 'python3 '.$int_script.' --source='.$path.'.src'.' --input='.$path.'.in';

	if ($parse_only == true) {
		system($parse.' >'.$path.'.parser.out', $parser_rc);
		$parser_out = file_get_contents($path.'.parser.out');
		system('rm -rf '.$path.'.parser.out');
	} elseif ($int_only == true) {
		system($int.' >'.$path.'.int.out', $int_rc);
		$int_out =  file_get_contents($path.'.int.out');
		system('rm -rf '.$path.'.int.out');
	} else {
		$int = 'python3 '.$int_script.' --source='.$path.'.parser.out'.' --input='.$path.'.in';
		system($parse.' >'.$path.'.parser.out', $parser_rc);
		$parser_out = file_get_contents($path.'.parser.out');
		system($int.' >'.$path.'.int.out', $int_rc);
		$int_out =  file_get_contents($path.'.int.out');
		system('rm -rf '.$path.'.parser.out');
		system('rm -rf '.$path.'.int.out');
	}
	generate_test_html($path, htmlentities($src), htmlentities($parser_out), $parser_rc, htmlentities($int_out), $int_rc, htmlentities($test_out), $test_rc, htmlentities($test_in));

}

/* !
 * If file not exist: generate file
 */
function check_files($file_name, $directory) {
	foreach (array('.in', '.out', '.rc') as $file_ext) {
		if (is_file($directory.$file_name.$file_ext)) {
			continue;
		} else {
			touch($directory.$file_name.$file_ext);
			if($file_ext == '.rc') {
				file_put_contents($directory.$file_name.$file_ext, '0');
			}
		}
	}
}

/* !
 * Main loop
 */
function test($directory) {
	global $recursive;
	$file_array = array();
	foreach (scandir($directory) as $file) {
		if(preg_match('/^.*.src$/', $directory.$file)) {
			$file_name = explode('.',  $file);
			$file_name = $file_name[0];
			check_files($file_name, $directory);
			make_test($file_name, $directory);
		} elseif ($file == '.' || $file == '..') {
			continue;
		} elseif (is_dir($directory.$file) && $recursive == true) {
			test($directory.$file.'/');
		}
	}
}
?>
