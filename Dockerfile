FROM mediawiki:1.35.5

# Define the ResourceBasePath in MediaWiki as a variable name: ResourceBasePath
ENV ResourceBasePath /var/www/html

ARG BUILD_SMW

# Make sure that existing software are updated 
RUN apt-get update -y



#RUN apt-get install -y texlive-latex-base
#RUN apt-get install -y texlive-latex-extra
RUN rm -rf /var/lib/apt/lists/*


RUN apt-get upgrade
#RUN apt-get install -y composer
# Change file read/write access for the images directory
RUN chmod -R 777 ${ResourceBasePath}/images

# Install lua for ARM architecture
RUN apt-get update -y 
RUN apt-get install lua5.1

# Go to the ${ResourceBasePath} for working directory
#WORKDIR ${ResourceBasePath}

#RUN if [ "$BUILD_SMW" = "true" ]; then composer require mediawiki/semantic-media-wiki "~3.2"; fi