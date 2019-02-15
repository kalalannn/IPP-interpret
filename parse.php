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

function xml_instr($instruction){
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

function xml_attr($attr, $number, $type){
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
	$arg = explode('@', $attr);
	if(
		(count($arg) != 2) || !(
			in_array($arg[0], array('GF', 'TF', 'LF')) && 
				is_label($arg[1]) == OK
			)
		)
		return 22;
	xml_attr($attr, $number, 'var');
	return OK;
}

function next_attr(){
	$x = strtok(' ');
	if($x == '')
		return _EMPTY;
	return $x;
}

function symb_attr($attr, $number){
	$arg = explode('@', $attr);
	if(count($arg) != 2)
		return 22;
	switch($arg[0]){
		case 'int':
			if(preg_match('/[0-9]*/', $arg[1]) != 1) 	//is_int
				return 22;
			break;
		case 'bool':
			if(!($arg[1] == 'true' || $arg[1] == 'false')) //is_bool
				return 22;
			break;
		case 'nil':
			if($arg[1] != 'nil')	//is_nil
				return 22;
			break;
		case 'string':	// testovani v pythonu bude
			break;
		default:
			return var_attr($attr, $number);
	}
	xml_attr($arg[1], $number, $arg[0]);
	return OK;
}
function label_attr($attr, $number){
	if(is_label($attr) != OK)
		return 22;
	xml_attr($attr, $number, 'label');
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
			xml_instr($x);
			switch ($i){
				case 0:
					break;
				case 1:
					if(var_attr(trim(next_attr()), 1) != OK)
						return ERR;
					break;
				case 2:
					if(symb_attr(trim(next_attr()), 1) != OK)
						return ERR;
					break;
				case 3:
					if(label_attr(trim(next_attr()), 1) != OK)
						return ERR;
					break;
				case 4:
					if(var_attr(trim(next_attr()), 1) != OK)
						return ERR;
					if(symb_attr(trim(next_attr()), 2) != OK)
						return ERR;
					break;
				case 5:
					break;
				case 6:
					if(var_attr(trim(next_attr()), 1) != OK)
						return ERR;
					if(symb_attr(trim(next_attr()), 2) != OK)
						return ERR;
					if(symb_attr(trim(next_attr()), 3) != OK)
						return ERR;
					break;
				case 7:
					if(label_attr(trim(next_attr()), 1) != OK)
						return ERR;
					if(symb_attr(trim(next_attr()), 2) != OK)
						return ERR;
					if(symb_attr(trim(next_attr()), 3) != OK)
						return ERR;
					break;
			}
			xmlwriter_end_element($xw);
		}
	}
}

xmlwriter_end_element($xw); 		//program
xmlwriter_end_document($xw);		//document
echo xmlwriter_output_memory($xw);	//print
?>
