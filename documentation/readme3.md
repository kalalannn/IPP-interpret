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
