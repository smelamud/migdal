TABLES=cross_entries entries groups image_files users

%.converted.csv: %.csv ./convert.py
	./convert.py $*

all: $(TABLES:=.converted.csv)
