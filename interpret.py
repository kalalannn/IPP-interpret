#!/usr/bin/python3
from sys import argv
from sys import stderr
import re
import xml.etree.ElementTree as ET
err_line = 1

class Error(object):
    def par_error()   :  print("Error: Parameter, try --help");  exit(10)
    def bad_label()   : print("Error: Bad Label"); exit(52)
    def bad_types() : print("Error: Bad types of operands"); exit(53)
    def undefined_variable() : print("Error: Undefined variable"); exit(54)
    def frame_error() :  print("Error: Frame does not exists");  exit(55)
    def uninitilized_variable() : print("Error: Variable has not been initializated"); exit(56)
    def empty_stack() : print("Error: Stack is empty"); exit(56)
    def division_error() : print("Error: Division by zero"); exit(57)
    def bad_value() : print("Error: Bad value"); exit(57)
    def string_error() : print("Error: String error"); exit(58)

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
        print ('hete\n\n\n\\n\n\n')
        par_error()

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

    def get_tuple(self, *symb):
        if symb[0] == 'var':
            return self.get_val(symb[1]).split('@')
        else:
            return symb

    def createframe(self):
        self.temp_frame = {}

    def pushframe(self):
        if self.temp_frame == None:
            frame_error()
        self.stack_frames.append(self.temp_frame)
        self.temp_frame = None

    def popframe(self):
        try:
            self.temp_frame = self.stack_frames.pop()
        except IndexError:
            frame_error()

    def check_frame(self, frame):  # check if frame is not empty
        if frame == 'GF':
            pass
        elif frame == 'LF':
            if self.stack_frames == []:
                frame_error()
        elif frame == 'TF':
            if self.temp_frame ==  None:
                frame_error()

    def check_var(self, var):       # check if var was defined
        frame, name = var.split('@')
        self.check_frame(frame)
        if frame == 'GF':
            try :
                self.global_frame[name]
            except KeyError:
                undefined_variable()
        elif frame == 'LF':
            try:
                self.stack_frames[-1][name]
            except KeyError:
                undefined_variable()
        elif frame == 'TF':
            try:
                self.temp_frame[name]
            except KeyError:
                undefined_variable()

    def defvar(self, var):          # define var
        frame, name = var.split('@')
        self.check_frame(frame)
        if frame == 'GF':
            self.global_frame[name] = "None@None"
        elif frame == 'LF':
            self.stack_frames[-1][name] = "None@None"
        elif frame == 'TF':
            self.temp_frame[name] = "None@None"

    def get_val(self, var) -> str:         # get value of defined variable
        frame, name = var.split('@')
        self.check_frame(frame)
        self.check_var(var)

        if frame == 'GF':  # check <var> and frames
            return self.global_frame[name] 
        elif frame == 'LF':
            return self.stack_frames[-1][name]
        elif frame == 'TF':
            return self.temp_frame[name] 

    def update_val(self, val, *var): # update value of defined variable
        frame, name = var[1].split('@')
        self.check_frame(frame)
        self.check_var(var[1])

        if frame ==  'GF':
            self.global_frame[name] = val
        elif frame == 'LF':
            self.stack_frames[-1][name] = val
        elif frame == 'TF':
            self.temp_frame[name] = val

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

    def add_label(self, name, line):
        self.labels[name] = line 

    def run(self, run_line):
        temp = self.instr_queue[run_line:]
        for instr in temp:
            action = getattr(self, instr.attrib['opcode'].lower()+'_i')
            args = tuple((x.attrib['type'], x.text) for x in instr)
            ret = action(*args)
            if ret == 'return': #tuple(tuple(type, text))
                break;

    def get_line(self, name) -> int:
        try: 
            return int(self.labels[name])
        except KeyError:
            bad_label()

    def write_i(self, *args):
        typ, val = self.frames.get_tuple(*args[0])

        if typ == 'None':
            uninitilized_variable()
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
            bad_types()

    def createframe_i(self, *empty):
        self.frames.createframe()

    def popframe_i(self, *empty):
        self.frames.popframe()

    def pushframe_i(self, *empty):
        self.frames.pushframe()

    def defvar_i(self, *args):
        self.frames.defvar(args[0][1])

    def move_i(self, *args): # <var> <symb>
        typ, val = self.frames.get_tuple(*args[1])
        if typ == 'None':
            uninitilized_variable()
        self.frames.update_val(typ + '@' + val, *args[0])
        
    def pushs_i(self, *args):
        typ, val = self.frames.get_tuple(*args[0])
        if typ == 'None':
            uninitilized_variable()
        self.data_stack.append(typ + '@' + val)

    def pops_i(self, *args):
        try:
            val = self.data_stack.pop()
        except IndexError:
            empty_stack()
        self.frames.update_val(val, *args[0])

    def operation(self, operation, *args):
        typ1, val1 = self.frames.get_tuple(*args[1])

        if operation == 'not':
            if typ1 == 'bool':
                val = typ1 + '@' + str(not str2bool(val1)).lower()
                return val
            else:
                bad_types()

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
                bad_types()
        else :
            bad_types()

        if operation in ['<', '>', '==']:
            typ1 = 'bool'

        if (operation in ['+', '-', '*', '/'] and (typ1 != 'int' or typ2 != 'int')) or \
                (operation in ['concat'] and (typ1 != 'string' or typ2 != 'string')) or \
                (operation in ['and', 'or'] and (typ1 != 'bool' or typ2 != 'bool')):
            bad_types()

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
                division_error()
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
            bad_types()

        try:
            self.frames.update_val('string' + '@' + chr(int(val)), *args[0])
        except ValueError:
            string_error()

    def stri2int_i(self, *args):
        typ1, val1 = self.frames.get_tuple(*args[1])
        typ2, val2 = self.frames.get_tuple(*args[2])

        if typ1 != 'string' or typ2 != 'int':
            bad_types()

        val1 = str2str(val1)

        try:
            self.frames.update_val('int' + '@' + str(ord(val1[int(val2)])), *args[0])
        except IndexError:
            string_error()

    def exit_i(self, *args):
        typ, val = self.frames.get_tuple(*args[0])
        if typ != 'int' or not(0 <= int(val) <= 49):
            bad_value()
        exit(int(val))

    def type_i(self, *args):
        typ, val = self.frames.get_tuple(*args[1])
        if typ == 'None':
            typ = ""
        self.frames.update_val('string@' + typ, *args[0])

    def strlen_i(self, *args):
        typ, val = self.frames.get_tuple(*args[1])
        if typ != 'string':
            bad_types()
        self.frames.update_val('int@' + str(len(val)), *args[0])

    def getchar_i(self, *args):
        typ1, val1 = self.frames.get_tuple(*args[1])
        typ2, val2 = self.frames.get_tuple(*args[2])

        if typ1 != 'string' or typ2 != 'int':
            bad_types()
        try: 
            self.frames.update_val('string@' + val1[int(val2)], *args[0])
        except IndexError:
            string_error()

    def setchar_i(self, *args):
        typ0, val0 = self.frames.get_tuple(*args[0])
        typ1, val1 = self.frames.get_tuple(*args[1])
        typ2, val2 = self.frames.get_tuple(*args[2])
        if typ0 != 'string' or typ1 != 'int' or typ2 != 'string':
            bad_types()
        try: 
            val0 = list(val0)
            val0[int(val1)] = val2[0]
            self.frames.update_val('string@' + "".join(val0), *args[0])
        except IndexError:
            string_error()

    def break_i(self, *args):
        print('stack: ' + str(self.data_stack))
        print('globFrame: '+str(self.frames.global_frame))
        print('allFrames: '+str(self.frames.stack_frames))
        print('tempFrames: '+str(self.frames.temp_frame))
        print('labels: ' +str(self.labels))

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
                in_val = int(in_val)
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
            self.jump_i(args[0])

    def jumpifneq_i(self, *args):
        if self.operation('==', *args) == 'bool@false':
            self.jump_i(args[0])


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
            par_error()

    check_argv(source_file, input_file)
    root = ET.parse(source_file).getroot()
    machine = Machine(input_file)


    for instr in root:
        machine.instr_queue.append(instr)
        if instr.attrib['opcode'].lower() == 'label':
            machine.add_label(instr[0].text, instr.attrib['order'])

    machine.run(0)



if __name__ == "__main__":
    __main__()
