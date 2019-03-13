# Documentation of IPP 2018/2019, 1 task 
* Author: Nikolaj Vorobiev
* Login: xvorob00
* Version: 1.0
* Licence GNU GPL3

`parse.php` Parse source code in IPPcode19 and transfer it into xml-output.

## Usage: 
```bash
  php7.3 parse.php --help
  php7.3 parse.php < {IPPcode19.src}
```
## Instructions are sorted by operands like that:
  + {CREATEFRAME, PUSHFRAME, POPFRAME, RETURN, BREAK}
  + {DEFVAR, POPS} {var}
  + {PUSHS, EXIT, PRINT, WRITE} {symb}
  + {LABEL, CALL, JUMP} {label}
  + {MOVE, INT2CHAR, STRLEN, TYPE, NOT} {var} {symb}
  + {READ} {var} {typ}
  + {ADD, SUB, MUL, IDIV, LT, GT, EQ, AND, OR, STR2INT, CONCAT, GETCHAR, SETCHAR} {var} {symb} {symb}
  + {JUMPIFEQ, JUMPIFNEQ} {label} {symb} {symb}
