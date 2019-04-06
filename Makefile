all: interpret

parse:
	php7.3 ~/3_roc/ipp/IPP-interpret/parse.php < $(src) > $(src).parser.out

interpret: parse
	python3 ~/3_roc/ipp/IPP-interpret/interpret.py --source=$(src).parser.out

edit:
	vim ./tests/_git/int-only/my_tests/some.src


