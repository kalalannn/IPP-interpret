# Documentation of IPP 2018/2019, 1 task 
* Author: Nikolaj Vorobiev, xvorob00@stud.fit.vutbr.cz
* Login: xvorob00
* Version: 1.0
* Licence GNU GPL3

## Usage: 
```bash
  php7.3 parse.php --help
  php7.3 parse.php < IPPcode19.src
```

Parse source code in IPPcode19 and transfer it into xml-output.
  1. `Get token by token from file`
  2. `Using switch case to control instruction token`
  3. `Using attr functions to control attibutes`
  4. `Using xml functions to generate xml-output`
  
## Instructions are sorted by operands like that:
  0. {CREATEFRAME, PUSHFRAME, POPFRAME, RETURN, BREAK}
  1. {DEFVAR, POPS} {var}
  2. {PUSHS, EXIT, PRINT, WRITE} {symb}
  3. {LABEL, CALL, JUMP} {label}
  4. {MOVE, INT2CHAR, STRLEN, TYPE, NOT} {var} {symb}
  5. {READ} {var} {typ}
  6. {ADD, SUB, MUL, IDIV, LT, GT, EQ, AND, OR, STR2INT, CONCAT, GETCHAR, SETCHAR} {var} {symb} {symb}
  7. {JUMPIFEQ, JUMPIFNEQ} {label} {symb} {symb}
