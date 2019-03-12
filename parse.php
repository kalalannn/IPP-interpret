<?php
/* ==========================================================
 * Project: Interpretor IPPcode19
 * File: parse.php
 * Author: Nikolaj Vorobiev
 * E-mail: xvorob00@stud.fit.vutbr.cz
 * ==========================================================
 */

define('OK', 0);
define('PAR_ERROR', 10);	// Parametr error
define('HEAD_ERROR', 21);	// Head error
define('LEX_ERROR', 22); 	// Lex + Synt error
$instr_counter = 1; 		// Counter for order

function xml_init(){
	// Configuration
	global $xw ;
	$xw = xmlwriter_open_memory();
	xmlwriter_set_indent($xw, 1);
	xmlwriter_set_indent_string($xw, '    ');
	// Head
	xmlwriter_start_document($xw, '1.0', 'UTF-8');
	// Program
	xmlwriter_start_element($xw, 'program');
	// Language
	xmlwriter_start_attribute($xw, 'language');
	xmlwriter_text($xw, 'IPPcode19');
	xmlwriter_end_attribute($xw);
}

function xml_instr($instruction){
	global $xw, $instr_counter;
	// Instruction
	xmlwriter_start_element($xw, 'instruction');
	// Order
	xmlwriter_start_attribute($xw, 'order');
	xmlwriter_text($xw, $instr_counter++);
	xmlwriter_end_attribute($xw);
	// Opcode
	xmlwriter_start_attribute($xw, 'opcode');
	xmlwriter_text($xw, $instruction);
	xmlwriter_end_attribute($xw);
}

function xml_attr($attr, $number, $type){
	global $xw;
	// Argument
	xmlwriter_start_element($xw, 'arg'.$number);
	// Type
	xmlwriter_start_attribute($xw, 'type');
	xmlwriter_text($xw, $type);
	xmlwriter_end_attribute($xw);
	// Text
	xmlwriter_text($xw, $attr);

	xmlwriter_end_element($xw);
}

function var_attr($attr, $number){
	$arg = explode('@', $attr);
	if((count($arg) != 2) || !(
			in_array($arg[0], array('GF', 'TF', 'LF')) && 
				is_label($arg[1]) == OK)){
		return LEX_ERROR;
	}
	xml_attr($attr, $number, 'var');
	return OK;
}

function symb_attr($attr, $number){
	$arg = explode('@', $attr);
	if(count($arg) != 2)
		return LEX_ERROR;
	switch($arg[0]){
		case 'int':
			if(preg_match('/^[0-9]*$/', $arg[1]) != 1)//is_int
				return LEX_ERROR;
			break;
		case 'bool':
			if(!($arg[1] == 'true' || $arg[1] == 'false')) //is_bool
				return LEX_ERROR;
			break;
		case 'nil':
			if($arg[1] != 'nil')	//is_nil
				return LEX_ERROR;
			break;
		case 'string':
			if(preg_match("/^([\\\\]\d{3}|[^\\\\\#\s])*$/", $arg[1]) != 1) 	//is_int
				return LEX_ERROR;
			break;
		default:
			return var_attr($attr, $number);
	}
	xml_attr($arg[1], $number, $arg[0]);
	return OK;
}

function label_attr($attr, $number){
	if(is_label($attr) != OK)
		return LEX_ERROR;
	xml_attr($attr, $number, 'label');
	return OK;
}

function type_attr($attr, $number){
	if(in_array($attr, array('int', 'string', 'bool'))){
		xml_attr($attr, $number, 'type');
		return OK;
	}
	return LEX_ERROR;
}

function is_label($attr){
	if(preg_match('/^[a-zA-z\_\-\$\&\%\*\!\?][0-9a-zA-z\_\-\$\&\%\*\!\?]*$/', 
		$attr) != 1)
		return LEX_ERROR;
	return OK;
}

function after_instr($tok){
	if($tok == '' || preg_match("/^[ ]*[#].*$/", $tok))
		return OK;
	else 
		return LEX_ERROR;
}

function _main($argc, $argv){
	if($argc == 2 && $argv[1] != '--help'){
		return PAR_ERROR;
	}
	elseif($argc == 2 && $argv[1] == '--help'){
		echo ("****|=========================================|****\n".
			  "    |   Help page for IPPcode19 parser part   |\n".
			  "    |=========================================|\n".
			  "    |                                         |\n".
			  "    |Version: 1.0                             |\n".
			  "    |Autor: Nikolaj Vorobiev                  |\n".
			  "    |Email: xvorob00@stud.fit.vutbr.cz        |\n".
			  "    |                                         |\n".
			  "    |Usage:                                   |\n".
			  "    |     php7.3 < <source code in IPPCode19> |\n".
			  "    |=========================================|\n".
			  ""
		);
		return OK;
	}

	if(fgets(STDIN, 11) != '.IPPcode19'){
		return HEAD_ERROR;
	}

	global $xw;
	xml_init();

	$glob_array = array(
				array('CREATEFRAME', 'PUSHFRAME', 'POPFRAME',
					'RETURN', 'BREAK'),
				array('DEFVAR', 'POPS'), 					//var
				array('PUSHS', 'EXIT', 'DPRINT', 'WRITE'), 	//symb
				array('LABEL', 'CALL', 'JUMP'), 			//label
				array('MOVE', 'INT2CHAR', 'STRLEN', 'TYPE', 'NOT'),
				array('READ'),
				array('ADD', 'SUB', 'MUL', 'IDIV', 'LT', 
					'GT', 'EQ', 'AND', 'OR', 'STR2INT', 
					'CONCAT', 'GETCHAR', 'SETCHAR'),
				array('JUMPIFEQ', 'JUMPIFNEQ')
	);

	while($line = fgets(STDIN)){
		if($line == "\n"){
			continue;
		}
		elseif(preg_match("/^[ ]*[#].*$/", $line)){
			continue;
		}
		else{
			echo "line: '".$line."'\n";
			$arr = preg_split('/\s+/', $line);
			$arr[0] = strtoupper($arr[0]);
			foreach($arr as $i){
				echo "tok: '".$i."'\n";
			}
			for ($i=0; $i<count($glob_array); $i++){
				if (in_array($arr[0], $glob_array[$i])){
					xml_instr($arr[0]);
					switch ($i){
						case 0:
							if (after_instr($arr[1])){
								return LEX_ERROR;
							}
							break;
						case 1:
							if(var_attr($arr[1], 1) != OK ||
								after_instr($arr[2]) != OK
							){
								return LEX_ERROR;
							}
							break;
						case 2:
							if(symb_attr($arr[1], 1) != OK ||
								after_instr($arr[2]) != OK
							){
								return LEX_ERROR;
							}
							break;
						case 3:
							if(label_attr($arr[1], 1) != OK ||
								after_instr($arr[2]) != OK
							){
								return LEX_ERROR;
							}
							break;
						case 4:
							if(	
								var_attr($arr[1], 1) != OK ||
								symb_attr($arr[2], 2) != OK ||
								after_instr($arr[3]) != OK
							)
								return LEX_ERROR;
							break;
						case 5:
							if(
								var_attr($arr[1], 1) != OK  ||
								type_attr($arr[2], 2) != OK ||
								after_instr($arr[3]) != OK
							)
								return LEX_ERROR;
							break;
						case 6:
							if(
								var_attr($arr[1], 1) != OK  ||
								symb_attr($arr[2], 2) != OK ||
								symb_attr($arr[3], 3) != OK ||
								after_instr($arr[4]) != OK
							)
								return LEX_ERROR;
							break;
						case 7:
							if(
								label_attr($arr[1], 1) != OK ||
								symb_attr($arr[2], 2) != OK  ||
								symb_attr($arr[3], 3) != OK  ||
								after_instr($arr[4]) != OK
							)
								return LEX_ERROR;
							break;
					}
					xmlwriter_end_element($xw);
					break;
				}
				elseif($i == count($glob_array) - 1){
					return LEX_ERROR;
				}
				else {
					continue;
				}
			}
		}
	}
	xmlwriter_end_element($xw); 		//program
	xmlwriter_end_document($xw);		//document
	echo xmlwriter_output_memory($xw);	//print
	return OK;
}
$code = _main($argc, $argv);
if ($code != 0){
	fprintf(STDERR, $code);
	echo "\n";
}
exit($code);
?>
