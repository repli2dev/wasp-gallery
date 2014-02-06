/**
 * Wasp Gallery
 * Simple lightweight gallery for showing all pictures in given folder.
 */
(function (jQuery) {
	// Extend jQuery to provide method for creating a new gallery
	jQuery.fn.waspGallery = function(settings) {
		// Check if there are any instances
		if(this.length == 0 && typeof this[0].id !== 'undefined') {
			return;
		}
		new WG('#' + this[0].id, settings);
	};

	// Gallery object (object instance for gallery instance)
	function WG(object, settings) {
		// Merge settings and add object reference
		jQuery.extend(this.settings, settings);
		this.selector = object;
		// Perform initialization
		this.init();
	}
	WG.prototype.settings = {
		apiPath: '',				// Path to api.php file
		defaultGallery: '',			// Default gallery to show
		defaultLanguage: 'en',		// Default language to be used
		showGalleries: true,		// Toogle whether show gallery
		showGalleryHeading: true,	// Toogle whether show gallery heading when showing gallery
		slideshowSpeed: 3500		// Time between pictures in slideshow mode
	}
	WG.languageStrings = {}
	WG.prototype.cssSelectors = {
		galleryList: 'wg-galleries-list',
		galleryImages: 'wg-gallery-images',
		errorMessage: 'wg-error-message',
		infoMessage: 'wg-info-message',
		listHeading: 'wg-list-heading',
		heading: 'wg-heading',
		description: 'wg-description',
		thumb: 'wg-thumb',
		thumbReal: 'wg-thumb-real',
		options: 'wg-options',
		hide: 'wg-hide',
		full: 'wg-full',
		ajaxPending: 'wg-ajax-pending'
	}
	WG.prototype.selector = null;	// CSS ID of <div> associated with this gallery instance
	WG.prototype.cacheGalleries = null;
	WG.prototype.cacheImages = null;
	WG.prototype.currentGallery = null;
	/**
	 * Inits
	 */
	WG.prototype.init = function() {
		// Init AJAX notification
		this.initAjaxNotification();
		// Create basic layout
		this.prepareLayout();
		// Load language
		this.loadLanguage();
		// Load all galleries
		this.loadGalleries();
		// Set default gallery (if any)
		if(typeof this.settings.defaultGallery !== 'undefined') {
			this.showGallery(this.settings.defaultGallery);
		} else {
			// Restore state (if any)
			this.restoreState();
		}
	}
	WG.prototype.initAjaxNotification = function() {
		var that = this;
		$(document).ajaxStart(function() {
			if ($(that.selector + ' .' + that.cssSelectors.ajaxPending).length == 0){
				var object = $(that.selector);
				object.prepend('<div class="' + that.cssSelectors.ajaxPending + '">' + that.translate('Gallery is loading. Please wait.') +'</div>');
			}
		});
		$(document).ajaxStop(function() {
			$(that.selector + ' .' + that.cssSelectors.ajaxPending).remove();
		});
	}
	WG.prototype.initColorbox = function(slideshow) {
		var colorboxSettings = {
			rel: this.cssSelectors.thumbReal,
			width: "85%",
			height: "85%",
			slideshowSpeed: this.settings.slideshowSpeed,
			current: this.translate('image {current} of {total}'),
			previous: this.translate('previous'),
			next: this.translate('next'),
			close: this.translate('close'),
			imgError: this.translate('This image failed to load.'),
			slideshowStart: this.translate('Start slideshow'),
			slideshowStop: this.translate('Stop slideshow')
		}
		if(slideshow) {
			colorboxSettings.slideshow = true;
		}
		$('.' + this.cssSelectors.thumbReal).colorbox(colorboxSettings);
		if(slideshow) {
			$('.' + this.cssSelectors.thumbReal + ':first').click();
		}
	}
	WG.prototype.prepareLayout = function() {
		var object = $(this.selector);
		object.empty();
		object.append('<div class="' + this.cssSelectors.galleryList + ' ' + ((!this.settings.showGalleries) ? this.cssSelectors.hide : '') + '"></div>');
		object.append('<div class="' + this.cssSelectors.galleryImages + ' ' + ((!this.settings.showGalleries) ? this.cssSelectors.full : '') + '"></div>');
	}
	WG.prototype.translate = function(message) {
		if(typeof this.languageStrings !== 'undefined' && this.languageStrings.hasOwnProperty(message)) {
			return this.languageStrings[message];
		}
		return message;
	}
	WG.prototype.populateMessage = function(selector, message, isInfo, prepend) {
		var className = this.cssSelectors.errorMessage;
		if(isInfo) {
			className = this.cssSelectors.infoMessage;
		}
		var object = $(selector);
		object.empty();
		if(typeof prepend !== 'undefined') {
			object.append(prepend);
		}
		object.append('<div class="' + className + '">' + this.translate(message) + '</div>');
	}
	WG.prototype.loadGalleries = function() {
		var that = this;
		$.ajax({
			url: this.settings.apiPath + '?request=galleries',
			success: function(data) {
				if(typeof data === 'object') {
					that.cacheGalleries = data;
				} else if(typeof data === 'string') {
					try {
						var parsed = JSON.parse(data);
						that.cacheGalleries = parsed;
					} catch(e) {
						that.cacheGalleries = null;
					}
				} else {
					that.cacheGalleries = null;
				}
				that.populateGalleries();
			},
			error: function() {
				that.populateMessage(this.selector + ' .' + that.cssSelectors.galleryList, 'Error when obtaining list of galleries. Please contact administrator of this webpage.');
			}
		});
	}
	WG.prototype.populateGalleries = function() {
		var object = $(this.selector + ' .' + this.cssSelectors.galleryList);
		if(this.cacheGalleries == null || this.cacheGalleries.length == 0 || typeof this.cacheGalleries.error != 'undefined') {
			this.populateMessage(this.selector + ' .' + this.cssSelectors.galleryList, 'No galleries.', true);
			return;
		}
		object.empty();
		object.append('<h2 class="' + this.cssSelectors.listHeading +'">' + this.translate('List of galleries') + '</h2>');
		ul = document.createElement('ul');
		object.append('<ul>');
		var that = this;
		for(var gallery in this.cacheGalleries) {
			var name = this.getGalleryName(gallery);
			var li = document.createElement('li');
			var anchor = document.createElement('a');
			anchor.innerHTML = name;
			anchor.onclick = function(arg) {
				return function() {
					that.showGallery(arg);
				}
			}(gallery);
			anchor.href = '#';
			li.appendChild(anchor);
			ul.appendChild(li);
		}
		object.append(ul);

		// Extra options
		var div = document.createElement('div');
		div.className = this.cssSelectors.options;
		var logout = document.createElement('a');
		logout.innerHTML = this.translate('Logout from galleries');
		logout.onclick = function() {
			that.logout();
		}
		logout.href = '#';
		div.appendChild(logout);
		div.appendChild(document.createElement('br'));
		var slideshow = document.createElement('a');
		slideshow.innerHTML = this.translate('Start slideshow');
		slideshow.onclick = function() {
			that.initColorbox(true);
		}
		slideshow.href = '#'
		div.appendChild(slideshow);
		if(!this.settings.showGalleries) {
			$(this.selector).append(div);
		} else {
			object.append(div);
		}
	}
	WG.prototype.logout = function() {
		$.ajax({
			url: this.settings.apiPath + '?request=logout'
		});
	}
	WG.prototype.showGallery = function(gallery) {
		this.currentGallery = gallery;
		this.loadImages();
	}
	WG.prototype.loadImages = function() {
		var that = this;
		$.ajax({
			url: this.settings.apiPath + '?request=images&gallery=' + that.currentGallery,
			success: function(data) {
				if(typeof data === 'object') {
					that.cacheImages = data;
				} else if(typeof data === 'string') {
					try {
						var parsed = JSON.parse(data);
						that.cacheImages = parsed;
					} catch(e) {
						that.cacheImages = null;
					}
				} else {
					that.cacheImages = null;
				}
				that.populateImages();
			},
			error: function() {
				that.populateMessage(this.selector + ' .' + that.cssSelectors.galleryImages, 'Error when obtaining gallery images. Please contact administrator of this webpage.');
			}
		});
	}
	WG.prototype.galleryAuthorization = function() {
		var password = prompt(this.translate('Enter password for this gallery: '));
		var that = this;
		var authorized = false;
		$.ajax({
			url: this.settings.apiPath + '?request=authorize&gallery=' + that.currentGallery + '&password=' + password,
			async: false,
			success: function(data) {
				if(typeof data === 'object') {
					that.cacheImages = data;
				} else if(typeof data === 'string') {
					try {
						data = JSON.parse(data);
					} catch(e) {
						// Intentionally, should not really happen
					}
				}
				if(typeof data.error === 'undefined' && data.error != 4) {
					authorized = true;
				}

			}
		});
		return authorized;
	}
	WG.prototype.populateImages = function() {
		// Check if not authorized
		if(typeof this.cacheImages.error !== 'undefined' && this.cacheImages.error == 3) {
			if(!this.galleryAuthorization()) {
				this.populateMessage(this.selector + ' .' + this.cssSelectors.galleryImages, 'Wrong password. Reselect gallery or refresh page for next try.', false, this.getGalleryHeading(this.currentGallery));
				return;
			} else {
				this.loadImages();
			}
		}
		var object = $(this.selector + ' .' + this.cssSelectors.galleryImages);
		if(this.cacheImages == null || this.cacheImages.length == 0 || typeof this.cacheImages.error != 'undefined') {
			this.populateMessage(this.selector + ' .' + this.cssSelectors.galleryImages, 'No images in this gallery.', true, this.getGalleryHeading(this.currentGallery));
			return;
		}
		object.empty();
		// Gallery info
		if(this.settings.showGalleryHeading) {
			object.append('<h2 class="' + this.cssSelectors.heading +'">' + this.getGalleryName(this.currentGallery) + '</h2>');
		}
		object.append('<div class="' + this.cssSelectors.description +'">' + this.getGalleryDescription(this.currentGallery) + '</div>');
		// The images
		for(var i = 0; i < this.cacheImages.length; i++) {
			var image = this.cacheImages[i];
			object.append('<div class="' + this.cssSelectors.thumb +'"><a class="' + this.cssSelectors.thumbReal + '" href="' + this.settings.apiPath + '?request=full&gallery=' + this.currentGallery +'&image=' + image + '"><img src="' + this.settings.apiPath + '?request=thumb&gallery=' + this.currentGallery +'&image=' + image + '" /></a></div>');
		}
		this.initColorbox();
		this.saveState();
	}
	WG.prototype.getGalleryName = function(gallery) {
		if(typeof this.cacheGalleries[gallery] == "undefined" || typeof this.cacheGalleries[gallery]['name'] == "undefined") {
			return gallery;
		} else {
			return this.cacheGalleries[gallery]['name'];
		}
	}
	WG.prototype.getGalleryDescription = function(gallery) {
		if(typeof this.cacheGalleries[gallery]['description'] == "undefined") {
			return "";
		} else {
			return this.cacheGalleries[gallery]['description'];
		}
	}
	WG.prototype.getGalleryHeading = function(gallery) {
		return '<h2 class="' + this.cssSelectors.heading +'">' + this.getGalleryName(gallery) + '</h2>'
	}
	WG.prototype.restoreState = function() {
		var hash = window.location.hash;
		if(hash.lastIndexOf('#wg-gallery-',0) === 0) {
			var gallery = hash.substring('#wg-gallery-'.length, hash.length);
			gallery = this.urldecode(gallery);
			this.showGallery(gallery);
		}
	}
	WG.prototype.saveState = function() {
		window.location.hash = 'wg-gallery-' + this.currentGallery;
	}
	WG.prototype.urldecode = function (url) {
		return decodeURIComponent(url.replace(/\+/g, ' '));
	}
	WG.prototype.loadLanguage = function() {
		var that = this;
		$.ajax({
			url: this.settings.apiPath + '?request=language&language=' + that.settings.defaultLanguage,
			async: false,
			success: function(data) {
				if(typeof data === 'string') {
					try {
						data = JSON.parse(data);
					} catch(e) {
						// Intentionally, should not really happen
					}
				}
				if(typeof data.error === 'undefined') {
					that.languageStrings = data;
				}

			}
		});
	}
}(jQuery));