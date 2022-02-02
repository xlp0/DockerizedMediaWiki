CURRENT_TIME = $(shell date +'%y.%m.%d %H:%M:%S')

test: 
	docker buildx build --platform linux/amd64,linux/arm64 -t xlp0/pkc_test --build-arg BUILD_SMW=false .

build:
	docker buildx build -t xlp0/pkc --build-arg BUILD_SMW=false .

buildAndPush: 
	docker buildx build --platform linux/amd64,linux/arm64 -t xlp0/pkc --build-arg BUILD_SMW=false . --push

push:
	docker push xlp0/pkc

build_no_cache: 
	docker buildx build --no-cache -t xlp0/pkc .

push_no_cache: 
	docker push xlp0/pkc

commitToGitHub:
	git add .
	git commit -m 'Created Makefile for the first time, and committed at ${CURRENT_TIME}'
	git push origin main
