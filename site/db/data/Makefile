TABLES=users

%.converted.csv: %.csv ./convert.py
	./convert.py $*

all: $(TABLES:=.converted.csv)
