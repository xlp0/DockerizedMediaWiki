FROM mediawiki:1.35.2

# Define the ResourceBasePath in MediaWiki as a variable name: ResourceBasePath
ENV ResourceBasePath /var/www/html

ARG BUILD_SMW

# Make sure that existing software are updated 
RUN apt-get update 
RUN apt-get install -y ghostscript
RUN apt-get install -y libmagickwand-dev
RUN apt-get install -y xpdf
RUN apt-get install -y xvfb
RUN apt-get install -y cron
RUN apt-get install -y nano
RUN apt-get install zlibc zip unzip
RUN rm -rf /var/lib/apt/lists/*

RUN apt-get upgrade

# Change file read/write access for the images directory
RUN chmod -R 777 ${ResourceBasePath}/images

# Define working directory for the following commands
WORKDIR ${ResourceBasePath}/extensions

RUN apt update
RUN apt install -y nodejs npm

# Copy 3d2png package to extensions/
COPY ./extensions/3d2png/ ${ResourceBasePath}/extensions/3d2png/

WORKDIR ${ResourceBasePath}/extensions/3d2png
RUN npm update
RUN npm upgrade
RUN npm install

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

# Copy the php.ini with desired upload_max_filesize into the php directory.
ENV PHPConfigurationPath /usr/local/etc/php
COPY ./resources/php.ini ${PHPConfigurationPath}/php.ini

# Copy two $wgLogo images to the container so that we can switch between them
COPY ./resources/xlp.png ${ResourceBasePath}/resources/assets/xlp.png
COPY ./resources/EuMuse.png ${ResourceBasePath}/resources/assets/EuMuse.png
COPY ./resources/toyhouse.png ${ResourceBasePath}/resources/assets/toyhouse.png
COPY ./resources/by-sa.png ${ResourceBasePath}/resources/assets/by-sa.png
COPY ./resources/by-sa.png ${ResourceBasePath}/resources/assets/aqua.png


# COPY ./resources/xlp.png ${ResourceBasePath}/backup/ToBeUploaded/xlp.png


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

# Install PHP package manager "Composer"

# Requires v1 instead of v2 for compatibility with Semantic MediaWiki 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer --version=2.1.2

# Update mediawiki extensions via composer
RUN echo "{\n\"require\": {\n\"mediawiki/semantic-media-wiki\": \"~3.2\"\n}\n}" > ${ResourceBasePath}/composer.local.json

# RUN useradd -u 5320 composer 
# USER composer
# RUN composer update --no-dev
# Warning: instsalling semantic mediawiki requires an additional 2GB of storage, it will make
# downloaind terribly slow. Do it with care.
RUN composer require mediawiki/maps

# Copy MW-OAuth2Client package to extensions/
COPY ./extensions/MW-OAuth2Client/ ${ResourceBasePath}/extensions/MW-OAuth2Client/

# Go to the ${ResourceBasePath}/extensions/MW-OAuth2Client/vendors/oauth2-client to install oauth-client
WORKDIR ${ResourceBasePath}/extensions/MW-OAuth2Client/vendors/oauth2-client

RUN composer install

# Copy Widgets package to extensions/
COPY ./extensions/Widgets/ ${ResourceBasePath}/extensions/Widgets/

# Go to the ${ResourceBasePath}/extensions/Widgets/ to install oauth-client
WORKDIR ${ResourceBasePath}/extensions/Widgets/

RUN chmod a+rw ${ResourceBasePath}/extensions/Widgets/compiled_templates

RUN composer update --no-dev

# Go to the ${ResourceBasePath} for working directory
WORKDIR ${ResourceBasePath}

RUN if [ "$BUILD_SMW" = "true" ]; then composer require mediawiki/semantic-media-wiki "~3.2"; fi
