#!/usr/bin/python3
from sys import argv
from sys import stderr
import re
import xml.etree.ElementTree as ET

class Except(Exception):
    pass
class ParamError(Except):
    pass
class BadLabel(Except):
    pass
class BadTypes(Except):
    pass
class BadValue(Except):
    pass
class UndefVar(Except):
    pass
class UninitVar(Except):
    pass
class FrameError(Except):
    pass
class EmptyStack(Except):
    pass
class StringError(Except):
    pass

class Error(object):
    def ParamError()   :  print("Error: Parameter, try --help");  exit(10) # ParamError
    def XmlError()   :  print("Error: Wrong XML Format");  exit(32) # XMLError 
    def BadLabel()   : print("Error: Bad Label"); exit(52)                 # BadLabel
    def BadTypes() : print("Error: Bad types of operands"); exit(53)       # BadTypes
    def UndefVar() : print("Error: Undefined variable"); exit(54) # UndefVar
    def FrameError() :  print("Error: Frame does not exists");  exit(55)   # FrameError
    def UninitVar() : print("Error: Variable has not been initializated"); exit(56) # UninitVar
    def EmptyStack() : print("Error: Stack is empty"); exit(56)            # EmptyStack
    def BadValue() : print("Error: Bad value"); exit(57)                   # BadValue
    def StringError() : print("Error: String error"); exit(58)             # StringError

def print_help():
    print(
        "****|===================================================|****\n"+
        "    |      Help page for IPPcode19 interpret part       |\n"+
        "    |===================================================|\n"+
        "    |                                                   |\n"+
        "    |Version: 1.0                                       |\n"+
        "    |Autor: Nikolaj Vorobiev                            |\n"+
        "    |Email: xvorob00@stud.fit.vutbr.cz                  |\n"+
        "    |                                                   |\n"+
        "    |Usage:                                             |\n"+
        "    |  python3 interpret.py **argv                      |\n"+
        "    |  **argv:                                          |\n"+
        "    |    --help                                         |\n"+
        "    |    --source=<sourceXml>                           |\n"+
        "    |    --input=<inputFile>                            |\n"+
        "****|===================================================|****\n"+
        ""
    )
    exit(0)

def check_argv(source_file, input_file):
    if source_file == input_file == None:
        raise ParamError

def str2bool(var) -> bool :
    return var.lower() in ['true']

def str2str(val) -> str:
    val = val.split('\\')
    res = val[0]
    for x in val[1:]:
        res += chr(int(x[0:3])) + x[3:]
    return res

class Frames(object):
    def __init__(self):
        self.global_frame = {}    # GF
        self.stack_frames = []    # stack is clean
        self.temp_frame = None    # temporary frame does not exist

    def createframe(self):
        self.temp_frame = {}

    def pushframe(self):
        if self.temp_frame == None:
            raise FrameError
        self.stack_frames.append(self.temp_frame)
        self.temp_frame = None

    def popframe(self):
        try:
            self.temp_frame = self.stack_frames.pop()
        except IndexError:
            raise FrameError

    def def_var(self, var):          # define var
        frame, name = var.split('@')
        if frame == 'GF':
            self.global_frame[name] = "None@None"
        elif frame == 'LF':
            try:
                self.stack_frames[-1][name] = "None@None"
            except IndexError:
                raise FrameError
        elif frame == 'TF':
            try:
                self.temp_frame[name] = "None@None"
            except TypeError:
                raise FrameError

    def check_var(self, var):       # check if var was defined  # var = 'FR@val'
        frame, name = var.split('@')
        if frame == 'GF':
            try:
                self.global_frame[name] 
            except KeyError:
                raise UndefVar
        elif frame == 'LF':
            try:
                self.stack_frames[-1][name]
            except IndexError:
                raise FrameError
            except KeyError:
                raise UndefVar
        elif frame == 'TF':
            try:
                self.temp_frame[name]
            except TypeError:
                raise FrameError
            except KeyError:
                raise UndefVar

    def get_val(self, var) -> str:         # get value of defined variable
        frame, name = var.split('@')
        self.check_var(var)  

        if frame == 'GF':  # check <var> and frames
            return self.global_frame[name]
        elif frame == 'LF':
            return self.stack_frames[-1][name]
        elif frame == 'TF':
            return self.temp_frame[name]

    def update_val(self, val, *var): # update value of defined variable
        self.check_var(var[1])

        frame, name = var[1].split('@')
        if frame ==  'GF':
            self.global_frame[name] = val
        elif frame == 'LF':
            self.stack_frames[-1][name] = val
        elif frame == 'TF':
            self.temp_frame[name] = val

    def get_tuple(self, *symb):
        if symb[0] == 'var':
            return self.get_val(symb[1]).split('@')
        else:
            return symb

class Machine(object):
    def __init__(self, input_file):
        self.frames = Frames()
        self.labels = {}
        self.instr_queue = []
        self.data_stack = []      # data stack
        self.input_text = []
        if input_file != None:
            with open(input_file) as f:
                self.input_text = f.readlines()
            self.input_text = [x.strip() for x  in self.input_text]
            self.input_text.reverse()

    def add_line(self, name, line):
        self.labels[name] = line 

    def get_line(self, name) -> int:
        try: 
            return int(self.labels[name])
        except KeyError:
            raise BadLabel

    def run(self, run_line):
        for instr in self.instr_queue[run_line:]:
            try:
                action = getattr(self, instr.attrib['opcode'].lower()+'_i')
            except AttributeError:
                Error.XmlError()

            args = tuple((x.attrib['type'], x.text) for x in instr)
            try:
                ret = action(*args)
                if ret == 'return': #tuple(tuple(type, text))
                    break;
            except Exception as Ex:
                err = "Order " + instr.attrib['order'] + ": " + instr.attrib['opcode']
                for arg in instr:
                    if arg.attrib['type'] == 'var':
                        err += ' ' + arg.text
                    else:
                        err += " {0}@{1}".format(arg.attrib['type'], arg.text)

                print(err)
                action = getattr(Error, type(Ex).__name__)
                action()

    def write_i(self, *args):
        typ, val = self.frames.get_tuple(*args[0])

        if typ == 'None':
            raise UninitVar
        elif typ == 'nil':
            return 
        elif typ == 'string':
            print(str2str(val), end='')
        else :
            print(val, end='')
            
    def dprint_i(self, *args):
        typ, val = self.frames.get_tuple(*args[0])

        if typ == 'int':
            stderr.write(val)
        else:
            raise BadTypes

    def createframe_i(self, *empty):
        self.frames.createframe()

    def popframe_i(self, *empty):
        self.frames.popframe()

    def pushframe_i(self, *empty):
        self.frames.pushframe()

    def defvar_i(self, *args):
        self.frames.def_var(args[0][1])

    def move_i(self, *args): # <var> <symb>
        typ, val = self.frames.get_tuple(*args[1])
        if typ == 'None':
            raise UninitVar
        self.frames.update_val(typ + '@' + val, *args[0])
        
    def pushs_i(self, *args):
        typ, val = self.frames.get_tuple(*args[0])
        if typ == 'None':
            raise UninitVar
        self.data_stack.append(typ + '@' + val)

    def pops_i(self, *args):
        try:
            val = self.data_stack.pop()
        except IndexError:
            raise EmptyStack
        self.frames.update_val(val, *args[0])

    def operation(self, operation, *args):
        typ1, val1 = self.frames.get_tuple(*args[1])

        if operation == 'not':
            if typ1 == 'bool':
                val = typ1 + '@' + str(not str2bool(val1)).lower()
                return val
            else:
                raise BadTypes

        typ2, val2 = self.frames.get_tuple(*args[2])

        if typ1 == typ2 == 'int':
            val1 = int(val1)
            val2 = int(val2)
        elif typ1 == typ2  == 'string':
            val1 = str2str(val1)
            val2 = str2str(val2)
        elif typ1 == typ2 == 'bool':
            val1 = str2bool(val1)
            val2 = str2bool(val2)
        elif typ1 == 'nil' or typ2 == 'nil':
            if operation != '==':
                raise BadTypes
        else:
                raise BadTypes

        if operation in ['<', '>', '==']:
            typ1 = 'bool'

        if (operation in ['+', '-', '*', '/'] and (typ1 != 'int' or typ2 != 'int')) or \
                (operation in ['concat'] and (typ1 != 'string' or typ2 != 'string')) or \
                (operation in ['and', 'or'] and (typ1 != 'bool' or typ2 != 'bool')):
            raise BadTypes

        if operation == '+' or operation == 'concat':
            val = str(val1 + val2)
        elif operation == '-': 
            val = str(val1 - val2)
        elif operation == '*':
            val = str(val1 * val2)
        elif operation == '/':
            try:
                val = str(int(val1) // int(val2))
            except ZeroDivisionError:
                raise BadValue
        elif operation == '<':
            val = str(val1 < val2).lower()
        elif operation == '>':
            val = str(val1 > val2).lower()
        elif operation == '==':
            val = str(val1 == val2).lower()
        elif operation == 'and':
            val = str(val1 and val2).lower()
        elif operation == 'or':
            val = str(val1 or val2).lower()

        return typ1 + '@' + val

    def concat_i(self, *args):
        self.frames.update_val(self.operation('concat', *args), *args[0])
    def add_i(self, *args):
        self.frames.update_val(self.operation('+', *args), *args[0])
    def sub_i(self, *args):
        self.frames.update_val(self.operation('-', *args), *args[0])
    def mul_i(self, *args):
        self.frames.update_val(self.operation('*', *args), *args[0])
    def idiv_i(self, *args):
        self.frames.update_val(self.operation('/', *args), *args[0])
        
    def lt_i(self, *args):
        self.frames.update_val(self.operation('<', *args), *args[0])
    def gt_i(self, *args):
        self.frames.update_val(self.operation('>', *args), *args[0])
    def eq_i(self, *args):
        self.frames.update_val(self.operation('==', *args), *args[0])

    def and_i(self, *args):
        self.frames.update_val(self.operation('and', *args), *args[0])
    def or_i(self, *args):
        self.frames.update_val(self.operation('or', *args), *args[0])
    def not_i(self, *args):
        self.frames.update_val(self.operation('not', *args), *args[0])

    def int2char_i(self, *args):
        typ, val = self.frames.get_tuple(*args[1])

        if typ != 'int':
            raise BadTypes
        try:
            self.frames.update_val('string' + '@' + chr(int(val)), *args[0])
        except ValueError:
            raise StringError

    def stri2int_i(self, *args):
        typ1, val1 = self.frames.get_tuple(*args[1])
        typ2, val2 = self.frames.get_tuple(*args[2])

        if typ1 != 'string' or typ2 != 'int':
            raise BadTypes

        val1 = str2str(val1)

        try:
            self.frames.update_val('int' + '@' + str(ord(val1[int(val2)])), *args[0])
        except IndexError:
            raise StringError

    def exit_i(self, *args):
        typ, val = self.frames.get_tuple(*args[0])
        if typ != 'int' or not(0 <= int(val) <= 49):
            raise BadValue
        exit(int(val))

    def type_i(self, *args):
        typ, val = self.frames.get_tuple(*args[1])
        if typ == 'None':
            typ = ""
        self.frames.update_val('string@' + typ, *args[0])

    def strlen_i(self, *args):
        typ, val = self.frames.get_tuple(*args[1])
        if typ != 'string':
            raise BadTypes
        self.frames.update_val('int@' + str(len(val)), *args[0])

    def getchar_i(self, *args):
        typ1, val1 = self.frames.get_tuple(*args[1])
        typ2, val2 = self.frames.get_tuple(*args[2])

        if typ1 != 'string' or typ2 != 'int':
            raise BadTypes
        try: 
            self.frames.update_val('string@' + val1[int(val2)], *args[0])
        except IndexError:
            raise StringError

    def setchar_i(self, *args):
        typ0, val0 = self.frames.get_tuple(*args[0])
        typ1, val1 = self.frames.get_tuple(*args[1])
        typ2, val2 = self.frames.get_tuple(*args[2])
        if typ0 != 'string' or typ1 != 'int' or typ2 != 'string':
            raise BadTypes
        try: 
            val0 = list(val0)
            val0[int(val1)] = val2[0]
            self.frames.update_val('string@' + "".join(val0), *args[0])
        except IndexError:
            raise StringError

    def break_i(self, *args):
        print("\n-------------------------------")
        print('stack: ' + str(self.data_stack))
        print('globFrame: '+str(self.frames.global_frame))
        print('allFrames: '+str(self.frames.stack_frames))
        print('tempFrames: '+str(self.frames.temp_frame))
        print('labels: ' +str(self.labels))
        print('input:'  +str(self.input_text))
        print('-------------------------------')

    def label_i(self, *args):
        pass

    def call_i(self, *args):
        self.run(self.get_line(args[0][1]))

    def jump_i(self, *args):
        self.run(self.get_line(args[0][1]))
        return 'return'

    def return_i(self, *args):
        return 'return'

    def read_i(self, *args):
        try:
            in_val = self.input_text.pop()
        except IndexError:
            in_val = input()

        typ, val = self.frames.get_tuple(*args[1])

        if val == 'int':
            try:
                in_val = str(int(in_val))
            except ValueError:
                in_val = '0'
        elif val == 'string':
            in_val = str(in_val)
        elif val == 'bool':
            if in_val.lower() == 'true':
                in_val = 'true'
            else:
                in_val = 'false'

        self.frames.update_val(val + '@' + in_val, *args[0])

    def jumpifeq_i(self, *args):
        if self.operation('==', *args) == 'bool@true':
            return self.jump_i(args[0])

    def jumpifneq_i(self, *args):
        if self.operation('==', *args) == 'bool@false':
            return self.jump_i(args[0])


def __main__():
    source_file = None
    input_file = None
    for arg in argv[1:]:
        if re.search(r'^--help$', arg):
            print_help()
        elif re.search(r'^--source=.+$', arg):
            source_file = arg.split('=')[1]
        elif re.search(r'^--input=.+$', arg):
            input_file = arg.split('=')[1]
        else:
            Error.param_error()

    check_argv(source_file, input_file)
    try:
        root = ET.parse(source_file).getroot()
    except ET.ParseError:
        Error.XmlError()

    machine = Machine(input_file)

    for instr in root:
        machine.instr_queue.append(instr)
        if instr.attrib['opcode'].lower() == 'label':
            machine.add_line(instr[0].text, instr.attrib['order'])

    machine.run(0)
    exit(0)

if __name__ == "__main__":
    __main__()
