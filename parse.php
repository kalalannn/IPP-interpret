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
	xmlwriter_start_attribute($xw, 'text');
	xmlwriter_text($xw, $attr);
	xmlwriter_end_attribute($xw);
	xmlwriter_end_element($xw);
}

function next_attr(){
	$x = strtok(' ');
	if($x == '')
		return OK;
	return $x;
}

function var_attr($attr, $number){
	$arg = explode('@', $attr);
	if(
		(count($arg) != 2) || !(
			in_array($arg[0], array('GF', 'TF', 'LF')) && 
				is_label($arg[1]) == OK
		)
	)
		return LEX_ERROR;
	xml_attr($attr, $number, 'var');
	return OK;
}

function symb_attr($attr, $number){
	$arg = explode('@', $attr);
	if(count($arg) != 2)
		return LEX_ERROR;
	switch($arg[0]){
		case 'int':
			if(preg_match('/[0-9]*/', $arg[1]) != 1)//is_int
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
			if(preg_match("/([\\][0-9][0-9][0-9])*[^\\\# ]*/", $arg[1]) != 1) 	//is_int
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
	$arg = explode('@', $attr);
	if(
		in_array($arg[0], array('int', 'string', 'bool')) &&
	   	$arg[1] == ''){
			xml_attr($arg[0], $number, 'type');
			return OK;
	}
	return LEX_ERROR;
}

function is_label($attr){
	if(preg_match('/[a-zA-z\_\-\$\&\%\*\!\?][a-zA-z\_\-\$\&\%\*\!\?0-9]+/', 
		$attr) != 1)
		return LEX_ERROR;
	return OK;
}

function _main($argc, $argv){
	if($argc == 2 && $argv[1] != '--help'){
		return PAR_ERROR;
	}
	elseif($argc == 2 && $argv[1] == '--help'){
		echo "help\n";
		return OK;
	}

	if(trim(fgets(STDIN)) != '.IPPcode19'){
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

	while($x = trim(strtoupper(strtok(fgets(STDIN), ' ')))){
		for ($i=0; $i<count($glob_array); $i++){
			if (in_array($x, $glob_array[$i])){
				xml_instr($x);
				switch ($i){
					case 0:
						break;
					case 1:
						if(var_attr(trim(next_attr()), 1) != OK)
							return LEX_ERROR;
						break;
					case 2:
						if(symb_attr(trim(next_attr()), 1) != OK)
							return LEX_ERROR;
						break;
					case 3:
						if(label_attr(trim(next_attr()), 1) != OK)
							return LEX_ERROR;
						break;
					case 4:
						if(	
							var_attr(trim(next_attr()), 1) != OK ||
							symb_attr(trim(next_attr()), 2) != OK
						)
							return LEX_ERROR;
						break;
					case 5:
						if(
							var_attr(trim(next_attr()), 1) != OK  ||
							type_attr(trim(next_attr()), 1) != OK
						)
							return LEX_ERROR;
						break;
					case 6:
						if(
							var_attr(trim(next_attr()), 1) != OK  ||
							symb_attr(trim(next_attr()), 2) != OK ||
							symb_attr(trim(next_attr()), 3) != OK 
						)
							return LEX_ERROR;
						break;
					case 7:
						if(
							label_attr(trim(next_attr()), 1) != OK ||
							symb_attr(trim(next_attr()), 2) != OK  ||
							symb_attr(trim(next_attr()), 3) != OK
						)
							return LEX_ERROR;
						break;
				}
				if(next_attr() == "\n"){
					xmlwriter_end_element($xw);
					break;
				}
				else
					return LEX_ERROR;
			}
			elseif(preg_match('/[#].*/', $x)){
				while(next_attr() != "\n"){}
			}
			elseif($i == count($glob_array) - 1){
				return LEX_ERROR;
			}
		}
	}
	xmlwriter_end_element($xw); 		//program
	xmlwriter_end_document($xw);		//document
	echo xmlwriter_output_memory($xw);	//print
	return OK;
}
exit (_main($argc, $argv));
?>
