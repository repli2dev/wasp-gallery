# WASP GALLERY

This small web application can handle creation of powerful gallery. However it
still remains very simple to use and modify.

Just upload your photos and everything else is taken care of.

## TABLE OF CONTENTS of this README

1. Usage
2. Requirements
3. Installation (backend)
4. Installation (frontend)
5. Customization
6. Translation
7. Licence
8. Contact

## 1. USAGE

1. Create folder (for safety use only alfanumeric characters)
   in directory `/galleries/`
2. Upload photos to this dir
3. Opinionaly add to this dir `file _name.txt` in which you can specify
   gallery name (otherwise only directory name would appear)
4. Opinionaly add to this dir `_info.txt` in which you can write (in HTML)
   description of the gallery, location where the photos were taken etc.
5. Opinionaly add to this dir `_pass.txt` in which you can write password
   to access this gallery. Please use only alphabet letters and numbers.
6. Browse your photos through browser

## 2. Requirements

* PHP >= 5.3
* GD library (for creating thumbnails)

## 3. INSTALLATION (BACKEND)

1. Copy all files from folder backend/ to proper place on server
2. Chmod dir `cache/` to `777`
3. Chmod dir `inc/temp/` to `777`
4. Test that `inc/config.neon` is not viewable through browser
5. Customize
6. Use!
7. !!!  If you want to use passworded galleries, please ensure, that galleries dir is not 
	accessable through browser
8. Check that galleries and cache are accessible for PHP (permissions etc).

## 4. INSTALLATION (FRONTEND)

The frontend is new fully Javascript with AJAX quering. See example in frontent/ajax

Available options for wasp-gallery object:

```
apiPath: '',			// Path on webserver to api.php
defaultGallery: '',		// Default gallery to load (default is no gallery)
defaultLanguage: 'en',	// Language used in current frontend
showGalleries: true, 	// Shows gallery list
showGalleryHeading: true, // Shows gallery heading
slideshowSpeed: 3500	// Time between image swaps
```


## 5. CUSTOMIZATION

* In file `inc/config.neon` you can set interesting options:
	- `gallery.dir = "galleries"` directory with galleries
	- `gallery.cache = "cache"` directory with thumbnails
	- `gallery.width = 160` width of thumbnail in pixels
	- `gallery.height = 140` height of thumbnail in pixels
* PHP code is mostly in `index.php` and `inc/function.php` and it use
  advantages of Nette framework (http://nette.org)
* Photo presentation uses colorbox and jquery
  http://colorpowered.com/colorbox/


## 6. TRANSLATION

Currently there are these translations:

* English
* Czech

However creating own is very simply:

1. Copy `inc/lang/cs.php` to `inc/lang/<YOUR-LANG>.php`
2. Translate all values from second column (do not touch keys!)
   e.g. `"key" => "values"`,
3. Send it to me, or make pull request on GitHub, so I can attach it to the next version ;-)

## 7. LICENCE


This application was written by Jan Drábek with handy usage of "cool"
frameworks.

Licence: New BSD licence
(only my parts; Nette, jquery, colorbox are licenced differently)

Copyright (c) 2010, Jan Drábek
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
	* Redistributions of source code must retain the above copyright
	  notice, this list of conditions and the following disclaimer.
	* Redistributions in binary form must reproduce the above copyright
	  notice, this list of conditions and the following disclaimer in the
	  documentation and/or other materials provided with the distribution.
	* Neither the name of the <organization> nor the
	  names of its contributors may be used to endorse or promote products
	  derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

## 8. CONTACT

If you encounter any problems do not hesitate to contact me!
Creating issue on GitHub repository is the preferred way.
