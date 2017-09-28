TABLES=cross_entries entries groups image_files image_file_transforms users

%.converted.csv: %.csv ./convert.py
	./convert.py $*

all: $(TABLES:=.converted.csv)
