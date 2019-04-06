#!/usr/bin/python3
from sys import argv
import re
import xml.etree.ElementTree as ET

def par_error()   :  print("Error: Parameter");  exit(10)
def undefined_variable() : print("Error: Undefined variable"); exit(54)
def frame_error() :  print("Error: Frame does not exists");  exit(55)
def uninitilized_variable() : print("Error: Variable has not been initializated"); exit(56)

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
        print("Error: Need at least one argv: --input or --source, --help")
        par_error()

def split_symb(var):
   return var.split('@')  # (frame, name)

class Frames(object):
    def __init__(self):
        self.global_frame = {}    # GF
        self.stack_frames = []    # stack is clean
        self.temp_frame = None    # temporary frame does not exist

    def createframe(self):
        self.temp_frame = {}

    def pushframe(self):
        if self.temp_frame is None:
            frame_error()
        self.stack_frames.append(self.temp_frame)
        self.temp_frame = None

    def popframe(self):
        try:
            self.temp_frame = self.stack_frames.pop()
        except IndexError:
            frame_error()

    def check_frame(self, frame):
        if frame == 'GF':
            pass
        elif frame == 'LF':
            if self.stack_frames == []:
                frame_error()
        elif frame == 'TF':
            if self.temp_frame ==  None:
                frame_error()

    def check_var(self, var):
        frame, name = split_symb(var)
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
        

    def defvar(self, var):
        frame, name = split_symb(var)
        self.check_frame(frame)
        if frame == 'GF':
            self.global_frame[name] = (None)
        elif frame == 'LF':
            self.stack_frames[-1][name] = (None)
        elif frame == 'TF':
            self.temp_frame[name] = (None)

    def get_val(self, var):
        frame, name = split_symb(var)
        self.check_frame(frame)
        self.check_var(var)

        if frame == 'GF':  # check <var> and frames
            return self.global_frame[name] 
        elif frame == 'LF':
            return self.stack_frames[-1][name]
        elif frame == 'TF':
            return self.temp_frame[name] 



    def update_val(self, var, val):
        frame, name = split_symb(var)
        self.check_frame(frame)
        self.check_var(var)

        if frame ==  'GF':
            self.global_frame[name] = val
        elif frame == 'LF':
            self.stack_frames[-1][name] = val
        elif frame == 'TF':
            self.temp_frame[name] = val

        

class Machine(object):
    def __init__(self):
        self.frames = Frames()
        self.data_stack = []      # data stack

    def run(self, instr):
        action = getattr(self, instr.attrib['opcode'].lower()+'_i')
        args = tuple((x.attrib['type'], x.text) for x in instr)
        action(*args) #tuple(tuple(type, text))

    def write_i(self, *args):
        pass
    def dwrite_i(self, *args):
        pass

    def createframe_i(self, *empty):
        self.frames.createframe()

    def popframe_i(self, *empty):
        self.frames.popframe()

    def pushframe_i(self, *empty):
        self.frames.pushframe()

    def defvar_i(self, *args):
        self.frames.defvar(args[0][1])

    def move_i(self, *args): # <var> <symb>
        if args[1][0] == 'var':
            val = self.frames.get_val(args[1][1])
            if val == (None):
                uninitilized_variable()
        else:
            val = args[1][0] + '@' + args[1][1]
        self.frames.update_val(args[0][1], val)

        
    def call_i(self, *args):
        pass
    def return_i(self, *args):
        pass
    def pushs_i(self, *args):
        self.data_stack.append(args[0])

    def pops_i(self, *args):
        try:
            self.data_stack.pop()
        except IndexError:
            pass


    def add_i(self, *args):
        pass
    def sub_i(self, *args):
        pass
    def mul_i(self, *args):
        pass
    def idiv_i(self, *args):
        pass
    def lt_i(self, *args):
        pass
    def gt_i(self, *args):
        pass
    def eq_i(self, *args):
        pass
    def and_i(self, *args):
        pass
    def or_i(self, *args):
        pass
    def not_i(self, *args):
        pass
    def int2char_i(self, *args):
        pass
    def stri2i_i(self, *args):
        pass
    def read_i(self, *args):
        pass
    def concat_i(self, *args):
        pass
    def strlen_i(self, *args):
        pass
    def getchar_i(self, *args):
        pass
    def setchar_i(self, *args):
        pass
    def type_i(self, *args):
        pass
    def label_i(self, *args):
        pass
    def jump_i(self, *args):
        pass
    def jumpifeq_i(self, *args):
        pass
    def jumpifneq_i(self, *args):
        pass
    def exit_i(self, *args):
        pass
    def break_i(self, *args):
        pass


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
            print("Error: Wrong parameters try --help")
            par_error()

    check_argv(source_file, input_file)
    root = ET.parse(source_file).getroot()
    machine = Machine()


    for instr in root:
        machine.run(instr)
    print('globFrame: '+str(machine.frames.global_frame))
    print('allFrames: '+str(machine.frames.stack_frames))
    print('tempFrames: '+str(machine.frames.temp_frame))


if __name__ == "__main__":
    __main__()
