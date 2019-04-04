#!/usr/bin/python3
from sys import argv
import re
import xml.etree.ElementTree as ET

def par_error() : print("ErrorL Parametr")exit(10)
def frame_error() : print("Error: Frame\n")exit(55)
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

class Machine(object):
    def __init__(self):
        self.global_frame = {}    # GF
        self.stack_frames = []    # stack is clean
        self.temp_frame = None    # temporary frame does not exist

    def write_i():
        pass
    def dwrite_i():
        pass

    def createframe_i():
        if(self.temp_frame != None):
            self.stack_frames.append(self.temp_frame)
        self.temp_frame = {}

    def popframe_i():
        try:
            self.temp_frame = self.stack_frames.pop()
        except IndexError:
            frame_error()

    def pushframe_i():
        if(self.temp_frame == None):
            frame_error()
        self.stack_frames.append(self.temp_frame)
        self.temp_frame = None

        pass
    def defvar_i():
        pass
    def move_i():
        pass
    def call_i():
        pass
    def return_i():
        pass
    def pushs_i():
        pass
    def pops_i():
        pass
    def add_i():
        pass
    def sub_i():
        pass
    def mul_i():
        pass
    def idiv_i():
        pass
    def lt_i():
        pass
    def gt_i():
        pass
    def eq_i():
        pass
    def and_i():
        pass
    def or_i():
        pass
    def not_i():
        pass
    def int2char_i():
        pass
    def stri2i_i():
        pass
    def read_i():
        pass
    def concat_i():
        pass
    def strlen_i():
        pass
    def getchar_i():
        pass
    def setchar_i():
        pass
    def type_i():
        pass
    def label_i():
        pass
    def jump_i():
        pass
    def jumpifeq_i():
        pass
    def jumpifneq_i():
        pass
    def exit_i():
        pass
    def break_i():
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

    #opcode = instr.attrib['opcode'].upper()

    """
    if (not (1 < len(argv) < 4)):
        print('Argument error')
    """

if __name__ == "__main__":
    __main__()
