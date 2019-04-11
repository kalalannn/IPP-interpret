# Documentation of IPP 2018/2019, 1 task Parser
Full technical documentation: `doc.pdf`
* Author: Nikolaj Vorobiev, xvorob00@stud.fit.vutbr.cz
* Login: xvorob00
* Version: 1.0
* License GNU GPL3

## Usage: 
```bash
  php7.3 parse.php --help
  php7.3 parse.php < IPPcode19.src
```
## How does it works?
Parse source code in IPPcode19 and transfer it into xml-output.
  1. `Get token by token from file`
  2. `Using switch case to control instruction token`
  3. `Using attr functions to control attibutes`
  4. `Using xml functions to generate xml-output`
  
## Regular expresions
  - string: `/^([\\\\]\d{3}|[^\\\\\#\s])*$/`
  - label: `/^[a-zA-z\_\-\$\&\%\*\!\?][0-9a-zA-z\_\-\$\&\%\*\!\?]*$/`
  - comments: `/^[ ]*[#].*$/`
  
## Instructions are sorted by operands like that:
  0. {CREATEFRAME, PUSHFRAME, POPFRAME, RETURN, BREAK}
  1. {DEFVAR, POPS} {var}
  2. {PUSHS, EXIT, DPRINT, WRITE} {symb}
  3. {LABEL, CALL, JUMP} {label}
  4. {MOVE, INT2CHAR, STRLEN, TYPE, NOT} {var} {symb}
  5. {READ} {var} {typ}
  6. {ADD, SUB, MUL, IDIV, LT, GT, EQ, AND, OR, STR2INT, CONCAT, GETCHAR, SETCHAR} {var} {symb} {symb}
  7. {JUMPIFEQ, JUMPIFNEQ} {label} {symb} {symb}


# Documentation of IPP 2018/2019, 2 task Interpret
* Author: Nikolaj Vorobiev, xvorob00@stud.fit.vutbr.cz
* Login: xvorob00
* Version: 1.0 
* License: GNU GPL3

## Usage 
```bash
  python3 interpret.py --help
  python3 interpret.py --input=<input_file> --source=<source_file>
```

## How does it works?
	Interprete XML file from Parser.
	1. Check parameters.
	2. Get instuctions from XML-file and append them to instruction stack.
	3. Create a dictionary of labels.
	4. Run instructions from instruction stack one by one.

## My solution and modifications
* All frames are in Frames class.
* All instruction methods are in Machine class.
* All errors are in Error class.
* Run loop: Machine.run(order).
* In Run loop get instuction's name and call equal Machine method.
* If Exception: transfer to Error in Run loop.
* If Error: write Order, Instruction, *args, type of error; exit with error code.
* Never return 31 error(Parser controls).
* CALL label: create new Run loop -> continue previous Run loop.
* CALL without RETURN also returns(at the end of the block).
* JUMPs label: return previous Run loop and create a new Run loop.
* RETURN without CALL is equal to EXIT int@0.
  
## Types of instructions 
* Output
* Frames
* Stack
* Operations
* Type
* String
* Control
* Debug


# Documentation of IPP 2018/2019, 3 task Test
* Author: Nikolaj Vorobiev, xvorob00@stud.fit.vutbr.cz
* Login: xvorob00
* Version: 1.0
* License: GNU GPL3

## Usage
```bash
php7.3 test.php *params
```

### Params(default)
``` bash
--help
--parse-only=false
--int-only=false
--parse-script=./parse.php
--int-script=./interpret.py
--directory=./
--recursive=false
```

## How does it works?
Depends on parameters, test.php will test parse or/and interpret scripts and generate output in html format.

## Html format
* Label(Tests output)
* Params
* Counts of different color tests.
* Red tests
* Other tests

## Tests format
* both: src, parser.out, parser.rc, int.out, test.in, test.out, int.rc, test.rc
* parse-only: src, parser.out, test.out, parser.rc, test.rc
* int-only: src, int.out, test.in, test.out, int.rc, test.rc

## Colors
Every test has his own color, depends on the test result.
```bash
GREEN : script.out == test.out && script.rc == test.rc
ORANGE : script.out != test.out && script.rc == test.rc != 0 
RED : (script.out != test.out && script.rc == test.rc == 0) ||  (script.rc != test.rc)
```
