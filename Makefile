APP=Test
AUTOGENS = $(APP)/is$(APP).db $(APP)/is$(APP).php $(APP)/is$(APP).js $(APP)/index.php

all: $(APP)

gitup:
	# To bring a repository here I did this:
	# cd ..; mkdir is; cd is;
	# git init 
	# git add .
	# git remote add origin https://github.com/tcveatch/is
	# To send changes to the global repository at github:
	# git status  # to see what has changed
	# git add <list modified files here> # this is called "staging"
	# git commit -m "Description of changes"
	# git push origin master

clean:
	rm -f error_log $(APP)/error_log templates/error_log /tmp/isphp.headers /tmp/isphp.err
	rm $(AUTOGENS)

tc: $(APP)
	rm -f error_log $(APP)/error_log templates/error_log /tmp/isphp.headers /tmp/isphp.err
	curl http://tomveatch.com/ops/is/$(APP)/is$(APP).php -X POST -T $(APP)/test.data.create -D /tmp/isphp.headers
	cat /tmp/isphp.headers
	touch error_log $(APP)/error_log templates/error_log /tmp/isphp.err
	cat   error_log $(APP)/error_log templates/error_log /tmp/isphp.err

tr: $(APP)
	rm -f error_log $(APP)/error_log templates/error_log /tmp/isphp.headers /tmp/isphp.err
	curl http://tomveatch.com/ops/is/$(APP)/is$(APP).php?id=46 -X GET -D /tmp/isphp.headers
	cat /tmp/isphp.headers
	touch error_log $(APP)/error_log templates/error_log /tmp/isphp.err
	cat   error_log $(APP)/error_log templates/error_log /tmp/isphp.err

tu: $(APP)
	rm -f error_log $(APP)/error_log templates/error_log /tmp/isphp.headers /tmp/isphp.err
	# curl http://tomveatch.com/ops/is/$(APP)/is$(APP).php -X PUT -T $(APP)/test.data.update -D /tmp/isphp.headers
	curl http://tomveatch.com/ops/is/$(APP)/is$(APP).php -X PUT -T $(APP)/test.data.update
	touch error_log $(APP)/error_log templates/error_log /tmp/isphp.err
	cat   error_log $(APP)/error_log templates/error_log /tmp/isphp.err

td: $(APP)
	rm -f error_log $(APP)/error_log templates/error_log /tmp/isphp.headers /tmp/isphp.err
	curl http://tomveatch.com/ops/is/$(APP)/is$(APP).php -X DELETE -T $(APP)/test.data.delete -D /tmp/isphp.headers
	curl http://tomveatch.com/ops/is/$(APP)/is$(APP).php?id=45 -X DELETE
	touch error_log $(APP)/error_log templates/error_log /tmp/isphp.err
	cat   error_log $(APP)/error_log templates/error_log /tmp/isphp.err

FYI:
	$(APP)/is$(APP).db create_table
	$(APP)/is$(APP).db modify_table
	$(APP)/is$(APP).db backup_table 
	$(APP)/is$(APP).db delete_table 
	$(APP)/is$(APP).db restore_table
	$(APP)/is$(APP).db create 
	$(APP)/is$(APP).db read id=1,2
	$(APP)/is$(APP).db update id=2 var=modval
	$(APP)/is$(APP).db delete id=2

$(APP): $(APP)/is$(APP).php $(APP)/is$(APP).js $(APP)/is$(APP).db $(APP)/index.php

$(APP)/$(APP).php: templates/is.php
	# Only do this step manually, and once, before editing for your own application
	# cp templates/is.php $(APP)/$(APP).php
	# Maybe save a copy as $(APP)/$(APP).php.safe after editing it.

$(APP)/index.php: templates/isindex.php $(APP)/$(APP).php
	php templates/isindex.php $(APP) > $(APP)/index.php

$(APP)/is$(APP).php: templates/isphp.php 
	php templates/isphp.php $(APP) > $(APP)/is$(APP).php

$(APP)/is$(APP).js: templates/isjs.php $(APP)/$(APP).php
	php templates/isjs.php $(APP) > $(APP)/is$(APP).js

$(APP)/is$(APP).db: templates/isdb.php $(APP)/$(APP).php
	php templates/isdb.php  $(APP) > $(APP)/is$(APP).db
	chmod 755 $(APP)/is$(APP).db
