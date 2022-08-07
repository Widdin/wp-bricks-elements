var lazyImageObserver;

function documentReady(fn) {
  if (document.readyState != 'loading'){ fn(); }
  else { document.addEventListener('DOMContentLoaded', fn); }
}

documentReady(function() {
	// Lazy load
	lazyImageObserver = new IntersectionObserver(function(entries, observer) {
		entries.forEach(function(entry) {
			if (entry.isIntersecting) {
				if (!entry.target.classList.contains('wbe-fadeIn')) {
					let lazyImage = entry.target.getElementsByTagName('img')[0];

					lazyImage.src = lazyImage.dataset.src;
					lazyImage.srcset = lazyImage.dataset.srcset;

					lazyImage.removeAttribute('data-src');
					lazyImage.removeAttribute('data-srcset');

					entry.target.classList.remove('wbe-lazy');
					lazyImageObserver.unobserve(entry.target);

					entry.target.classList.add('wbe-fadeIn');
					entry.target.classList.remove('wbe-loading');
				}

			}
		});
	});

	// Infinite Scroll
	let last_known_scroll_position = 0;
	let ticking = false;
	document.addEventListener('scroll', function(e) {
	  last_known_scroll_position = window.scrollY;

	  if (!ticking) {
		window.requestAnimationFrame(function() {
		  if (infiniteScroll() == false) {
			return;
		  }
		  ticking = false;
		});

		ticking = true;
	  }
	});

	// On filter change
	document.querySelectorAll('.wbe-posts-filter').forEach(e =>
		e.addEventListener('change', (event) => {
			sendAJAX();
	}));

	// On initial load
	var checkExist = setInterval(function() {
		if (document.getElementById('filterForm') && document.getElementById('filterForm').length) {
			sendAJAX();
			clearInterval(checkExist);
		}
	}, 100);
});

function loadMore() {
	disableLoadMore(true);

	const loadMore = document.querySelector('.wbe-posts__load-more');
	const data = { 
		paged: loadMore.dataset.nextPage 
	};

	sendAJAX(data);
}
		
function sendAJAX(loadMoreData) {
	if (!loadMoreData) {
		var containers = document.getElementsByClassName('wbe-post');
		for (let container of containers) {
			container.classList.add('wbe-fadeOut');
		}
	}
	
	var filter = document.getElementById('filterForm');
	var request = new XMLHttpRequest();
	request.open(filter.attributes.method.textContent, filter.attributes.action.textContent, true);
	request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
	
	var startTime = new Date().getTime();
	
	request.onload = function () {
		if (this.status >= 200 && this.status < 400) { // Sucess

			var endTime = new Date().getTime();
			var totalTime = endTime - startTime;
			var timeToWait = 0;

			if (totalTime >= 400) { timeToWait = 0; } 
			else { timeToWait = 400 - totalTime; }

			var response = this.response;

			setTimeout(function(){

				if (loadMoreData) {
					document.getElementsByClassName('wbe-posts')[0].insertAdjacentHTML('beforeend', response);
					incrementLoadMore();
					disableLoadMore(false);
				} else {
					var responseContainer = document.getElementById('wbe-response');
					responseContainer.innerHTML = response;
				}
				
				updateObserver();
				
				setTimeout(function(){
					enableButtons();
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

	if (loadMoreData) {
		url = Object.keys(loadMoreData).map(function(k) { return encodeURIComponent(k) + '=' + encodeURIComponent(loadMoreData[k]) }).join('&')
		queryString += '&' +  url;
	}

	disableButtons();
	request.send(queryString);
}

function updateObserver() {
	const lazyImages = [].slice.call(document.querySelectorAll('.wbe-post'));
	lazyImages.forEach(function(lazyImage) { lazyImageObserver.observe(lazyImage); });
} 

function disableButtons() {
	const buttons = document.querySelectorAll('.wbe-radio-toolbar label');
	buttons.forEach(function(e) { e.classList.add('disabled'); });

	const filters = document.querySelectorAll('.wbe-posts-filter');
	filters.forEach(function(e) { e.disabled = true; });
}

function enableButtons() {
	const buttons = document.querySelectorAll('.wbe-radio-toolbar label');
	buttons.forEach(function(e) { e.classList.remove('disabled'); });

	const filters = document.querySelectorAll('.wbe-posts-filter');
	filters.forEach(function(e) { e.disabled = false; });
}

function incrementLoadMore() {
	const loadMore = document.querySelector('.wbe-posts__load-more');
	loadMore.dataset.currentPage++;
	loadMore.dataset.nextPage++;

	if (loadMore.dataset.currentPage == loadMore.dataset.maxPage) {
		loadMore.style.visibility = 'hidden';
	}
}

function infiniteScroll() {
	const isInfinite = document.querySelector('input[name=infinite_scroll]').value === '1';
	
	if (isInfinite) {
		var loadMoreButton = document.getElementsByClassName("wbe-posts__load-more")[0];
		
		if (loadMoreButton) {
			const isDisabled = loadMoreButton.disabled;
			const isHidden = loadMoreButton.style.visibility === 'hidden';
			const offsetTop = (loadMoreButton.getBoundingClientRect().top + document.documentElement.scrollTop);
			
			if( (document.scrollingElement.scrollTop + window.innerHeight ) >= offsetTop && !isDisabled && !isHidden){
				disableLoadMore(true);
				var event = document.createEvent('HTMLEvents');
				event.initEvent('click', true, false);
				loadMoreButton.dispatchEvent(event);
			}
		}
	} else {
		return false;
	}
}

function disableLoadMore(disabled) {
	var loadMore = document.getElementsByClassName("wbe-posts__load-more")[0];
	loadMore.disabled = disabled;
	
	if (disabled) { loadMore.innerHTML = "Loading..."; } 
	else { loadMore.innerHTML = "Load More"; }
}
