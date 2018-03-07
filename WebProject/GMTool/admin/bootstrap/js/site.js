$(document).ready(function(){
    $("img.lazy").unveil();

    $.scrollUp({
          scrollName: 'scrollUp', // Element ID
          topDistance: '300', // Distance from top before showing element (px)
          topSpeed: 300, // Speed back to top (ms)
          animation: 'fade', // Fade, slide, none
          animationInSpeed: 200, // Animation in speed (ms)
          animationOutSpeed: 200, // Animation out speed (ms)
          scrollText: '<i class="fa fa-angle-up"></i>', // Text for element
          activeOverlay: false  // Set CSS color to display scrollUp active point, e.g '#00FFFF'
    });

    $('#toc').toc({
	    'listType': '<ul class="nav"></ul>',
	    'selectors': 'h2', //elements to use as headings
	    'container': '.post-content', //element to find all selectors in
	    'smoothScrolling': true, //enable or disable smooth scrolling on click
	    'prefix': 'toc', //prefix for anchor tags and class names
	    'onHighlight': function(el) {}, //called when a new section is highlighted 
	    'highlightOnScroll': true, //add class to heading that is currently in focus
	    'highlightOffset': 100, //offset to trigger the next headline
	    'anchorName': function(i, heading, prefix) { //custom function for anchor name
	        return prefix+i;
	    }
	});
  });