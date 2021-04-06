CURRENT_TIME = $(shell date +'%y.%m.%d %H:%M:%S')
build: 
	docker build -t xlp0/mediawiki .

push:
	docker push xlp0/mediawiki

commitToGitHub:
	git add .
	git commit -m 'Created Makefile for the first time, and committed at ${CURRENT_TIME}'
	git push origin main