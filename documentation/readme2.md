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

