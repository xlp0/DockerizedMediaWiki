# Extension: FreeTex

A very simple extension for Mediawiki which connects Mediawiki to LaTeX backend (Texlive used here).

## Initial design

Some users want to use TikZ package (for LaTeX) to draw graphs directly in Mediawiki, however, Math extension only
supports formulas (using $$...$$) and PGFTikZ extension was no longer available for Mediawiki above 1.25.
Texvc has restrictions on the format and has a fixed template, which means that using '\usepackage{}'
is not possible.

Since existing tools are unable to address this problem, I decided (actually forced to) write this tool which 
allows users to freely write LaTeX code, converting the compiled pdf document into .png images and showing them
on pages of Mediawiki. And that's why it is called FreeTex.

## Environemt requirements

- texlive: texlive along with necessary packages are required, and please make sure that command 'pdflatex' is
executable (executable file 'pdflatex' should exist in /usr/bin/ directory)
- ImageMagick: although this tool is probably pre-installed in Ubuntu (16.04 or higher), make sure that 'convert' is
executable and it can successfully convert a .pdf document into a .png image 
(this might require some changes in the configuration)

## Parameters

Using 'density' and 'quality' parameters in the label in Mediawiki can modify the feature of the picture.

## Installation

You should first figure out where the pictures are stored in your Mediawiki, and modify the directions
in the FreeTex.php file.

This extension is specially designed for XLP system currently, for more details, please take a look at 
http://toyhouse.cc:81/index.php/LaTeX_and_TikZ
