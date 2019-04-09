dire  = /home/kalalannn/3_roc/ipp/IPP-interpret
directory = $(dire)/tests/_git/int-only/my_tests

all: interpret



parse:
	php7.3 $(dire)/parse.php < $(directory)/$(src).src > $(directory)/$(src).src.parser.out

interpret: parse
	python3 $(dire)/interpret.py --source=$(directory)/$(src).src.parser.out

edit:
	vim $(directory)/$(src).src

input_test: parse
	python3 $(dire)/interpret.py --source=$(directory)/$(src).src.parser.out --input=$(directory)/$(src).in

ls:
	ls $(directory)

clean:
	rm -rf $(directory)/*.parser.out


