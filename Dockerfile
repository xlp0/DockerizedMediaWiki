FROM mediawiki:1.35.2

# Define the ResourceBasePath in MediaWiki as a variable name: ResourceBasePath
ENV ResourceBasePath /var/www/html

ARG BUILD_SMW

# Make sure that existing software are updated 
RUN apt-get update -y
#RUN apt-get install -y zlibc
RUN apt-get install -y zip unzip
RUN apt update


RUN apt-get install -y ghostscript
RUN apt-get install -y libmagickwand-dev
RUN apt-get install -y xpdf
RUN apt-get install -y xvfb
RUN apt-get install -y graphviz
RUN apt-get install -y mscgen
RUN apt-get install -y cron
RUN apt-get install -y nano


#RUN apt-get install -y texlive-latex-base
#RUN apt-get install -y texlive-latex-extra
RUN rm -rf /var/lib/apt/lists/*


RUN apt-get upgrade
#RUN apt-get install -y composer
# Change file read/write access for the images directory
RUN chmod -R 777 ${ResourceBasePath}/images

# Define working directory for the following commands
#WORKDIR ${ResourceBasePath}/extensions

RUN apt update
RUN apt-get install -y nodejs npm
RUN npm i npm@latest -g
RUN npm update
RUN npm upgrade

# Copy 3d2png package to extensions/
COPY ./extensions/3d2png/ ${ResourceBasePath}/extensions/3d2png/
WORKDIR ${ResourceBasePath}/extensions/3d2png
#RUN npm install 


# Copy 3D package to extensions/
COPY ./extensions/3D/ ${ResourceBasePath}/extensions/3D/

# Copy Math package to extensions/
COPY ./extensions/Math/ ${ResourceBasePath}/extensions/Math/

# Copy 3DAlloy package to extensions/
COPY ./extensions/3DAlloy/ ${ResourceBasePath}/extensions/3DAlloy/

# Copy StlHandler package to extensions/
COPY ./extensions/StlHandler/ ${ResourceBasePath}/extensions/StlHandler/

# Copy MultimediaViewer package to extensions/
COPY ./extensions/MultimediaViewer/ ${ResourceBasePath}/extensions/MultimediaViewer/

# Copy intersection package to extensions/
COPY ./extensions/intersection/ ${ResourceBasePath}/extensions/intersection/

# Copy PdfHandler package to extensions/
COPY ./extensions/PdfHandler/ ${ResourceBasePath}/extensions/PdfHandler/

# Copy PDFEmbed package to extensions/
COPY ./extensions/PDFEmbed/ ${ResourceBasePath}/extensions/PDFEmbed/

# Copy PDFEmbed package to extensions/
COPY ./extensions/EmbedVideo/ ${ResourceBasePath}/extensions/EmbedVideo/

# Copy the BackupAndRestore scripting package to MediaWiki's "extensions/" directory
COPY ./extensions/BackupAndRestore/ ${ResourceBasePath}/extensions/BackupAndRestore/

# Copy Matomo package to extensions/
COPY ./extensions/Matomo/ ${ResourceBasePath}/extensions/Matomo/

# Copy OAuth package to extensions/
COPY ./extensions/OAuth/ ${ResourceBasePath}/extensions/OAuth/

# Copy GeoData package to extensions/
COPY ./extensions/GeoData/ ${ResourceBasePath}/extensions/GeoData/

# Copy JsonConfig package to extensions/
COPY ./extensions/JsonConfig/ ${ResourceBasePath}/extensions/JsonConfig/

# Copy Kartographer package to extensions/
COPY ./extensions/Kartographer/ ${ResourceBasePath}/extensions/Kartographer/

# Copy EmbedSpotify package to extensions/
COPY ./extensions/EmbedSpotify/ ${ResourceBasePath}/extensions/EmbedSpotify/

# Copy PageForms package to extensions/
COPY ./extensions/PageForms/ ${ResourceBasePath}/extensions/PageForms/

# Copy GoogleDocs4MW package to extensions/
COPY ./extensions/GoogleDocs4MW/ ${ResourceBasePath}/extensions/GoogleDocs4MW/

# Copy TemplateWizard package to extensions/
COPY ./extensions/TemplateWizard/ ${ResourceBasePath}/extensions/TemplateWizard/

# Copy Cargo package to extensions/
COPY ./extensions/Cargo/ ${ResourceBasePath}/extensions/Cargo/

# Copy HeadScript package to extensions/
COPY ./extensions/HeadScript/ ${ResourceBasePath}/extensions/HeadScript/

# Copy DrawioEditor package to extensions/
COPY ./extensions/DrawioEditor/ ${ResourceBasePath}/extensions/DrawioEditor/

# Copy Matomo package to extensions/
COPY ./extensions/Matomo/ ${ResourceBasePath}/extensions/Matomo/

# Copy MatomoAnalytics package to extensions/
COPY ./extensions/MatomoAnalytics/ ${ResourceBasePath}/extensions/MatomoAnalytics/

# Copy PGFTikZ package to extensions/
COPY ./extensions/PGFTikZ  ${ResourceBasePath}/extensions/PGFTikZ

# Copy ExtensionDataAccounting package to extensions/
COPY ./extensions/ExtensionDataAccounting  ${ResourceBasePath}/extensions/ExtensionDataAccounting

# Copy TemplateData package to extensions/
COPY ./extensions/TemplateData  ${ResourceBasePath}/extensions/TemplateData

# Copy TemplateStyles package to extensions/
COPY ./extensions/TemplateStyles  ${ResourceBasePath}/extensions/TemplateStyles

# Copy FreeTeX package to extensions/
COPY ./extensions/FreeTeX  ${ResourceBasePath}/extensions/FreeTeX

# Copy ExternalData package to extensions/
COPY ./extensions/ExternalData  ${ResourceBasePath}/extensions/ExternalData

# Copy HTMLTags package to extensions/
COPY ./extensions/HTMLTags  ${ResourceBasePath}/extensions/HTMLTags

# Copy HTMLets package to extensions/
COPY ./extensions/HTMLets  ${ResourceBasePath}/extensions/HTMLets

# Copy NamespaceHTML package to extensions/
COPY ./extensions/NamespaceHTML  ${ResourceBasePath}/extensions/NamespaceHTML

# Copy CSS package to extensions/
COPY ./extensions/CSS  ${ResourceBasePath}/extensions/CSS

# Copy Diagrams package to extensions/
COPY ./extensions/Diagrams  ${ResourceBasePath}/extensions/Diagrams


# Copy Medik package to extensions/
COPY ./extensions/Medik  ${ResourceBasePath}/skins/Medik

# Copy Refreshed package to extensions/
COPY ./extensions/Refreshed  ${ResourceBasePath}/skins/Refreshed

# Copy the php.ini with desired upload_max_filesize into the php directory.
ENV PHPConfigurationPath /usr/local/etc/php
COPY ./resources/php.ini ${PHPConfigurationPath}/php.ini

# Copy two $wgLogo images to the container so that we can switch between them
COPY ./resources/xlp.png ${ResourceBasePath}/resources/assets/xlp.png
COPY ./resources/EuMuse.png ${ResourceBasePath}/resources/assets/EuMuse.png
COPY ./resources/toyhouse.png ${ResourceBasePath}/resources/assets/toyhouse.png
COPY ./resources/by-sa.png ${ResourceBasePath}/resources/assets/by-sa.png
COPY ./resources/aqua.png ${ResourceBasePath}/resources/assets/aqua.png


# Copy the mime.types to the container
COPY ./resources/mime.types ${ResourceBasePath}/includes/mime.types

# Copy the mime.info to the container
COPY ./resources/mime.info ${ResourceBasePath}/includes/mime.info

# The service cron start instruction should be kicked off by the "up.sh" script
# Directly use the following CMD here always cause the MediaWiki service to hang.
# CMD service cron start

# Add crontab file in the cron directory
ADD crontab /var/spool/cron/crontab/root

# Give execution rights on the cron job
RUN chmod 0644 /var/spool/cron/crontab/root

# Run the cron job
RUN crontab /var/spool/cron/crontab/root

# Go to the ${ResourceBasePath} for working directory
WORKDIR ${ResourceBasePath}


# Requires v1 instead of v2 for compatibility with Semantic MediaWiki 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer --version=2.1.6

# Copy MW-OAuth2Client package to extensions/
COPY ./extensions/MW-OAuth2Client/ ${ResourceBasePath}/extensions/MW-OAuth2Client/

# Go to the ${ResourceBasePath}/extensions/MW-OAuth2Client/vendors/oauth2-client to install oauth-client
WORKDIR ${ResourceBasePath}/extensions/MW-OAuth2Client/vendors/oauth2-client

RUN composer install

# Copy Widgets package to extensions/
COPY ./extensions/Widgets/ ${ResourceBasePath}/extensions/Widgets/

# Go to the ${ResourceBasePath}/extensions/Widgets/ to install Widgets
WORKDIR ${ResourceBasePath}/extensions/Widgets/

RUN chmod a+rw ${ResourceBasePath}/extensions/Widgets/compiled_templates

RUN composer install

COPY ./composer.local.json ${ResourceBasePath}/composer.local.json

WORKDIR ${ResourceBasePath}

RUN composer install

RUN composer update --no-dev

# Go to the ${ResourceBasePath} for working directory
#WORKDIR ${ResourceBasePath}

#RUN if [ "$BUILD_SMW" = "true" ]; then composer require mediawiki/semantic-media-wiki "~3.2"; fi