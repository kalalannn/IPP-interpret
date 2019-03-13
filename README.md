Documentation of IPP 2018/2019, 1 task 
======================================
### Author: Nikolaj Vorobiev
### Login: xvorob00
--------------------------------------
## Parser of IPP2019Code: 
    - parse.php
## Usage: 
  - php7.3 parse.php --help
  - php7.3 parse.php < {IPPcode19.src}
## How does it works?
  Parse source code in IPPcode19 and transfer it into xml-output.
## Instructions are sorted by operands like that:
  + {CREATEFRAME, PUSHFRAME, POPFRAME, RETURN, BREAK}
  + {DEFVAR, POPS} {var}
  + {PUSHS, EXIT, PRINT, WRITE} {symb}
  + {LABEL, CALL, JUMP} {label}
  + {MOVE, INT2CHAR, STRLEN, TYPE, NOT} {var} {symb}
  + {READ} {var} {typ}
  + {ADD, SUB, MUL, IDIV, LT, GT, EQ, AND, OR, STR2INT, CONCAT, GETCHAR, SETCHAR} {var} {symb} {symb}
  + {JUMPIFEQ, JUMPIFNEQ} {label} {symb} {symb}
