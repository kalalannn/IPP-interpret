<?php
define('OK', 0);
define('_EMPTY', 1);
define('HEAD_ERROR', 21);
define('ERR', 22); 	// SYNT + LEX ERRORS
$instr_counter = 1; // Order
// Configuration
$xw = xmlwriter_open_memory();
xmlwriter_set_indent($xw, 1);
xmlwriter_set_indent_string($xw, ' ');
// Head
xmlwriter_start_document($xw, '1.0', 'UTF-8');
// Program
xmlwriter_start_element($xw, 'program');
// Language
xmlwriter_start_attribute($xw, 'language');
xmlwriter_text($xw, 'IPPcode19');
xmlwriter_end_attribute($xw);

function instr_xml($instruction){
	global $xw, $instr_counter;
	// Instruction
	xmlwriter_start_element($xw, 'instruction');
	// Attributes
	xmlwriter_start_attribute($xw, 'order');
	xmlwriter_text($xw, $instr_counter++);
	xmlwriter_end_attribute($xw);

	xmlwriter_start_attribute($xw, 'opcode');
	xmlwriter_text($xw, $instruction);
	xmlwriter_end_attribute($xw);
}

function next_attr(){
	$x = strtok(' ');
	if($x == '')
		return _EMPTY;
	return $x;
}
function attr_xml($attr, $number, $type){
	global $xw;
	xmlwriter_start_element($xw, 'arg'.$number);
	xmlwriter_start_attribute($xw, 'type');
	xmlwriter_text($xw, $type);
	xmlwriter_end_attribute($xw);

	xmlwriter_start_attribute($xw, 'text');
	xmlwriter_text($xw, $attr);
	xmlwriter_end_attribute($xw);
	xmlwriter_end_element($xw);
}

function var_attr($attr, $number){
	if($attr == _EMPTY)
		return 22;
	if(is_var($attr) != OK)
		return 22;
	attr_xml($attr, $number, 'var');
	return OK;
}


function is_var($attr){
	$arg = explode('@', $attr);
	if(count($arg) != 2)
		return 22;
	if(in_array($arg[0], array('GF', 'TF', 'LF')) && is_label($arg[1]) == OK)
		return OK;
	else 
		return 22;
}

function is_symb($attr, $number){
	$arg = explode('@', $attr);
	if(count($arg) != 2)
		return 22;
	switch($arg[0]){
		case 'int':
			if(preg_match('/[0-9]*/', $arg[1]) != 1)
				return 22;
			attr_xml($arg[1], $number, $arg[0]);
			break;
		case 'bool':
			if(!($arg[1] == 'true' || $arg[1] == 'false'))
				return 22;
			attr_xml($arg[1], $number, $arg[0]);
			break;
		case 'nil':
			if($arg[1] != 'nil')
				return 22;
			attr_xml($arg[1], $number, $arg[0]);
			break;
		case 'string':	// testovani v pythonu bude
			attr_xml($arg[1], $number, $arg[0]);
			break;
		default:
			if(in_array($arg[0], array('GF', 'TF', 'LF')))
				return var_attr($attr, $number);
			else 
				return 22;
			break;
				
	}
	return OK;
}

function is_label($attr){
	if(preg_match('/[a-zA-z\_\-\$\&\%\*\!\?][a-zA-z\_\-\$\&\%\*\!\?0-9]*/', 
		$attr) != 1)
		return 22;
	return OK;
}

/*
if($argc == 2 && $argv[1] == '--help'){
	echo ("some");
}
 */
if(trim(fgets(STDIN)) != '.IPPcode19'){
	exit (HEAD_ERROR);
}



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

//var_dump($glob_array); 

while($x = trim(strtoupper(strtok(fgets(STDIN), ' ')))){
	for ($i=0; $i<count($glob_array); $i++){
		if (in_array($x, $glob_array[$i])){
			instr_xml($x);
			switch ($i){
				case 0:
					break;
				case 1:
					if(var_attr(trim(next_attr()), 1) != OK)
						return 22;
					break;
				case 2:
					if(is_symb(trim(next_attr()), 1) != OK)
						break;
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
					break;
			}
			xmlwriter_end_element($xw);
		}
	}
	strtok('');
	strtok('');
}

xmlwriter_end_element($xw); 		//program
xmlwriter_end_document($xw);		//document
echo xmlwriter_output_memory($xw);	//print
?>
