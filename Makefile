CURRENT_TIME = $(shell date +'%y.%m.%d %H:%M:%S')

build: 
	docker build -t xlp0/mediawiki .

push:
	docker push xlp0/mediawiki

build_no_cache: 
	docker build --no-cache -t xlp0/mediawiki .

push_no_cache: 
	docker push xlp0/mediawiki

commitToGitHub:
	git add .
	git commit -m 'Created Makefile for the first time, and committed at ${CURRENT_TIME}'
	git push origin main