CURRENT_TIME = $(shell date +'%y.%m.%d %H:%M:%S')

build: 
	docker build -t emhavis/pkc_semanticwiki:v1.37.1 --build-arg BUILD_SMW=true .

buildAndPush:
	docker build -t emhavis/pkc_semanticwiki --build-arg BUILD_SMW=false .
	docker push emhavis/pkc_semanticwiki:v0.6

buildAndPushSMW: 
	docker build -t emhavis/pkc_semanticwiki --build-arg BUILD_SMW=true .
	docker push emhavis/pkc_semanticwiki

push:
	docker push emhavis/pkc_semanticwiki:v1.37.1

build_no_cache: 
	docker build --no-cache -t emhavis/pkc_semanticwiki:v0.6 .

push_no_cache: 
	docker push emhavis/pkc_semanticwiki:v0.6

commitToGitHub:
	git add .
	git commit -m 'Created Makefile for the first time, and committed at ${CURRENT_TIME}'
	git push origin main
