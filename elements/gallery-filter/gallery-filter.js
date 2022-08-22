var wbeGalleryLazyImageObserver;
var wbeGallery;
var wbeGalleryMasonry;

function documentReady(fn) {
	if (document.readyState != 'loading'){ fn(); }
	else { document.addEventListener('DOMContentLoaded', fn); }
}

documentReady(function () {
	var checkExist = setInterval(function() {
		if (document.getElementById('filter') && document.getElementById('filter').length) {
			wbeGallery = document.querySelector('.wbe-gallery');

			wbeGalleryInitImageObserver();
			
			wbeGallerySendAJAX();
			
			wbeGalleryOnFilterChange();
			
			clearInterval(checkExist);
		}
	}, 100);
});

function wbeGalleryOnFilterChange() {
	document.querySelectorAll('.wbe-gallery__gallery-filter').forEach(e => 
		e.addEventListener('change', (event) => {
			wbeGallerySendAJAX();
	}));
}

function wbeGalleryInitImageObserver() {
	wbeGalleryLazyImageObserver = new IntersectionObserver(function(entries, observer) {
		entries.forEach(function(entry) {
			if (entry.isIntersecting) {
				let lazyImage = entry.target;
				
				lazyImage.src = lazyImage.dataset.src;
				lazyImage.srcset = lazyImage.dataset.srcset;
				
				lazyImage.removeAttribute('data-src');
				lazyImage.removeAttribute('data-srcset');
				
				lazyImage.classList.remove("lazy");
				wbeGalleryLazyImageObserver.unobserve(lazyImage);
				
				lazyImage.classList.add('wbe-gallery__fadeIn');
				lazyImage.classList.remove("wbe-gallery__loading");
			}
		});
	});
}

function wbeGallerySendAJAX() {
	var filter = document.getElementById('filter');
	var request = new XMLHttpRequest();
	request.open(filter.attributes.method.textContent, filter.attributes.action.textContent, true);
	request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
	
	var startTime = new Date().getTime();
	
	request.onload = function () {
		if (this.status >= 200 && this.status < 400) {

			var endTime = new Date().getTime();
			var totalTime = endTime - startTime;
			var timeToWait = 0;

			if (totalTime >= 400) { timeToWait = 0; } 
			else { timeToWait = 400 - totalTime; }

			var response = this.response;

			setTimeout(function(){

				var responseContainer = document.getElementById('response');
				responseContainer.innerHTML = response;
				
				wbeGalleryCreateMasonry();
				
				wbeGalleryUpdateObserver();
				
				setTimeout(function(){
					wbeGalleryEnableButtons();
				}, 800);
				
			}, timeToWait);

		} else {
			console.error("Request Failed: Response Error");
		}
	};
	request.onerror = function() {
		console.error("Request Failed: Connection Error");
	};

	var data = new FormData(filter);
	var queryString = new URLSearchParams(data).toString();

	wbeGalleryDisableButtons();
	
	const galleryImages = document.querySelectorAll('.wbe-gallery__image');
	galleryImages.forEach(function(e) { e.classList.add('wbe-gallery__fadeOut'); });
	
	request.send(queryString);
}

function wbeGalleryCreateMasonry() {
	if (!bricksIsFrontend) { 
		wbeGallery = document.querySelector('.wbe-gallery'); 
	}

	if ( wbeGallery ) {
		wbeGalleryMasonry = new Masonry( wbeGallery, {
			itemSelector: '.wbe-gallery__image',
			gutter: 20,
			percentPosition: true,
			horizontalOrder: true
		});
	}
}

function wbeGalleryUpdateObserver() {
	let lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));
	lazyImages.forEach(function(lazyImage) { wbeGalleryLazyImageObserver.observe(lazyImage); });
}

function wbeGalleryDisableButtons() {
	const buttons = document.querySelectorAll('.wbe-gallery__radio-toolbar label');
	buttons.forEach(function(e) { e.classList.add('disabled'); });
	
	const filters = document.querySelectorAll('.wbe-gallery__gallery-filter');
	filters.forEach(function(e) { e.disabled = true; });
}

function wbeGalleryEnableButtons() {
	const buttons = document.querySelectorAll('.wbe-gallery__radio-toolbar label');
	buttons.forEach(function(e) { e.classList.remove('disabled'); });
	
	const filters = document.querySelectorAll('.wbe-gallery__gallery-filter');
	filters.forEach(function(e) { e.disabled = false; });
}
