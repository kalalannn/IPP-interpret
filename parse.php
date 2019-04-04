<?php
/*! 
 * @file
 * @brief Interpet IPPcode19 : Parser
 * @author Nikolaj Vorobiev
 * @version 1.0
 * @date 12.03.2019
 * @copyright GNU GPL3
 */

define('OK', 0);

/*! @brief PAR_ERROR --help parametr error. */
define('PAR_ERROR', 10);

/*! @brief PAR_ERROR '.IPPcode' head error. */
define('HEAD_ERROR', 21);

define('OPCODE_ERROR', 22);

/*! @brief ERROR Lexical and Syntax errors. */
define('ERROR', 23);

/*! @brief Global variable $instr_counter, that counts instructions. */
$instr_counter = 1;

/*! 
 * @defgroup xml_group
 * @brief Functions for generating xml-output
 * @{
 */	

/*!
 * @brief Creates xml document and start program.
 */
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

/*!
 * @brief XML instruction generator
 * @param[in] $instruction Instruction
 */
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

/*!
 * @brief XML attribute generator
 * @param[in] $attr Attribute
 * @param[in] $number Number of attribute
 * @param[in] $type Type of attribute
 */
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

/*!
 * @brief Ends program and xml document
 */
function xml_end(){
	global $xw;
	xmlwriter_end_element($xw); 		//program
	xmlwriter_end_document($xw);		//document
	echo xmlwriter_output_memory($xw);	//print
}
/*! @} */

/*! 
 * @defgroup attr_group
 * @brief Functions for checking attributes
 * @{
 */	

/*!
 * @brief Check and Generate {variable} attribute
 * @param[in] $attr Attribute
 * @param[in] $number Number of attribute 
 * @return ERROR: if attr is not {var} else OK
 */
function var_attr($attr, $number){
	$arg = explode('@', $attr);
	if((count($arg) != 2) || !(
			in_array($arg[0], array('GF', 'TF', 'LF')) && 
				is_label($arg[1]) == OK)){
		return ERROR;
	}
	xml_attr($attr, $number, 'var');
	return OK;
}

/*!
 * @brief Check and Generate {symbol} attribute
 * @param[in] $attr Attribute
 * @param[in] $number Number of attribute 
 * @return ERROR: if attr is not {symb} else OK
 */
function symb_attr($attr, $number){
	$arg = explode('@', $attr);
	if(count($arg) != 2)
		return ERROR;
	switch($arg[0]){
		case 'int':
			if(preg_match('/^[0-9]*$/', $arg[1]) != 1)//is_int
				return ERROR;
			break;
		case 'bool':
			if(!($arg[1] == 'true' || $arg[1] == 'false')) //is_bool
				return ERROR;
			break;
		case 'nil':
			if($arg[1] != 'nil')	//is_nil
				return ERROR;
			break;
		case 'string':
			if(preg_match("/^([\\\\]\d{3}|[^\\\\\#\s])*$/", $arg[1]) != 1) 	//is_int
				return ERROR;
			break;
		default:
			return var_attr($attr, $number);
	}
	xml_attr($arg[1], $number, $arg[0]);
	return OK;
}

/*!
 * @brief Check and Generate {label} attribute
 * @param[in] $attr Attribute
 * @param[in] $number Number of attribute 
 * @return ERROR: if attr is not {label} else OK
 */
function label_attr($attr, $number){
	if(is_label($attr) != OK)
		return ERROR;
	xml_attr($attr, $number, 'label');
	return OK;
}

/*!
 * @brief Check and Generate {type} attribute
 * @param[in] $attr Attribute
 * @param[in] $number Number of attribute 
 * @return {ERROR: if attr is not {type} else OK}
 */
function type_attr($attr, $number){
	if(in_array($attr, array('int', 'string', 'bool'))){
		xml_attr($attr, $number, 'type');
		return OK;
	}
	return ERROR;
}

/*!
 * @brief Check if attr is {label}
 * @param[in] $attr Attribute
 * @return ERROR: if attr is not {label} else OK
 */
function is_label($attr){
	if(preg_match('/^[a-zA-z\_\-\$\&\%\*\!\?][0-9a-zA-z\_\-\$\&\%\*\!\?]*$/', 
		$attr) != 1)
		return ERROR;
	return OK;
}

/*! 
 * @brief Check attribute after instruction
 * @param[in] $attr Attribute
 * @return ERROR: if attr is not '' or ' # {some_text}' else OK
 */
function after_instr($attr){
	if($attr == '' || preg_match("/^[ ]*[#].*$/", $attr))
		return OK;
	else 
		return ERROR;
}
/*! @} */

/*! 
 * @brief Print a help message
 */
function print_help(){
	echo (
			"****|===================================================|****\n".
			"    |      Help page for IPPcode19 parser part          |\n".
			"    |===================================================|\n".
			"    |                                                   |\n".
			"    |Version: 1.0                                       |\n".
			"    |Autor: Nikolaj Vorobiev                            |\n".
			"    |Email: xvorob00@stud.fit.vutbr.cz                  |\n".
			"    |                                                   |\n".
			"    |Usage:                                             |\n".
			"    |     php7.3 parse.php < {source code in IPPCode19} |\n".
			"    |     First Line: '.IPPcode19'                      |\n".
			"****|===================================================|****\n".
			""
		);
}

/*! 
 * @brief Main parse loop
 * @details Function contains glob_array variable, that contains instructions sorted by attributes.
 * @code{.php}
 * $glob_array = array(
 *     array('CREATEFRAME', 'PUSHFRAME', 'POPFRAME', 'RETURN', 'BREAK'),
 *     array('DEFVAR', 'POPS'), 					
 *     array('PUSHS', 'EXIT', 'DPRINT', 'WRITE'), 	
 *     array('LABEL', 'CALL', 'JUMP'), 			
 *     array('MOVE', 'INT2CHAR', 'STRLEN', 'TYPE', 'NOT'),
 *     array('READ'),
 *     array('ADD', 'SUB', 'MUL', 'IDIV', 'LT', 'GT', 'EQ', 'AND', 'OR', 'STR2INT', 'CONCAT', 'GETCHAR', 'SETCHAR'),
 *     array('JUMPIFEQ', 'JUMPIFNEQ')
 * );
 * @endcode
 * @details Also function contains parser's loop, where is going Lexical and Syntax analysis.
 */
function parse(){
	global $xw;
	xml_init();

	$glob_array = array(
				array('CREATEFRAME', 'PUSHFRAME', 'POPFRAME',
					'RETURN', 'BREAK'),
				array('DEFVAR', 'POPS'), 					
				array('PUSHS', 'EXIT', 'DPRINT', 'WRITE'), 	
				array('LABEL', 'CALL', 'JUMP'), 			
				array('MOVE', 'INT2CHAR', 'STRLEN', 'TYPE', 'NOT'),
				array('READ'),
				array('ADD', 'SUB', 'MUL', 'IDIV', 'LT', 
					'GT', 'EQ', 'AND', 'OR', 'STRI2INT', 
					'CONCAT', 'GETCHAR', 'SETCHAR'),
				array('JUMPIFEQ', 'JUMPIFNEQ')
	);

	while($line = fgets(STDIN)){
		$x = explode('#', $line);
		if(count($x) > 1){
			$line = $x[0]."\n";
		}

		if(preg_match('/^\s+$/', $line) || $line == ''){
			continue;
		}
		else{
			//echo "line: '".$line."'\n";
			$arr = preg_split('/\s+/', $line);
			$arr[0] = strtoupper($arr[0]);
			/*foreach($arr as $i){
				echo "tok: '".$i."'\n";
			}*/
			for ($i=0; $i<count($glob_array); $i++){
				if (in_array($arr[0], $glob_array[$i])){
					xml_instr($arr[0]);
					switch ($i){
						case 0:
							if (after_instr($arr[1])){
								return ERROR;
							}
							break;
						case 1:
							if(var_attr($arr[1], 1) != OK ||
								after_instr($arr[2]) != OK
							){
								return ERROR;
							}
							break;
						case 2:
							if(symb_attr($arr[1], 1) != OK ||
								after_instr($arr[2]) != OK
							){
								return ERROR;
							}
							break;
						case 3:
							if(label_attr($arr[1], 1) != OK ||
								after_instr($arr[2]) != OK
							){
								return ERROR;
							}
							break;
						case 4:
							if(	
								var_attr($arr[1], 1) != OK ||
								symb_attr($arr[2], 2) != OK ||
								after_instr($arr[3]) != OK
							)
								return ERROR;
							break;
						case 5:
							if(
								var_attr($arr[1], 1) != OK  ||
								type_attr($arr[2], 2) != OK ||
								after_instr($arr[3]) != OK
							)
								return ERROR;
							break;
						case 6:
							if(
								var_attr($arr[1], 1) != OK  ||
								symb_attr($arr[2], 2) != OK ||
								symb_attr($arr[3], 3) != OK ||
								after_instr($arr[4]) != OK
							)
								return ERROR;
							break;
						case 7:
							if(
								label_attr($arr[1], 1) != OK ||
								symb_attr($arr[2], 2) != OK  ||
								symb_attr($arr[3], 3) != OK  ||
								after_instr($arr[4]) != OK
							)
								return ERROR;
							break;
					}
					xmlwriter_end_element($xw);
					break;
				}
				elseif($i == count($glob_array) - 1){
					return OPCODE_ERROR;
				}
				else {
					continue;
				}
			}
		}
	}
	xml_end();
	return OK;
}

/*! 
 * @brief Main function of the program 
 * @details Enable parameters of main: --help
*/
function _main($argc, $argv){
	if($argc == 2 && $argv[1] != '--help'){
		return PAR_ERROR;
	}
	elseif($argc == 2 && $argv[1] == '--help'){
		print_help();
		return OK;
	}

	if(strtolower(fgets(STDIN, 11)) != '.ippcode19'){
		return HEAD_ERROR;
	}
	return parse();
}

/*! @brief Variable contains exit code of _main. */
$code = _main($argc, $argv);
if ($code != 0){
	fprintf(STDERR, $code);
	echo "\n";
}
exit($code);
?>
