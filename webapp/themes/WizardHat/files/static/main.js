// Create Namespaces
var x;
var mc = {};
mc.x = {};
mc.x.area = {};
mc.matrix = {};
mc.matrix.font = '24pt Roboto Mono';
mc.matrix.font2 = '29pt Roboto Mono';
mc.matrix.grid = {};
mc.matrix.grid.x = 20;
mc.matrix.grid.y = 20;
mc.matrix.active = [];
mc.matrix.io = false;
mc.matrix.firstDraw = true;
mc.matrix.wordset=[];
mc.loadingNewUrl = false;
mc.firstPageLoad = true;
mc.init = {};
mc.sounds = {};

mc.currentPage = 1;
mc.currentCategory = '';

mc.scramble = {};
mc.scramble.progress = 0;
mc.scrambledWriter = [];

mc.search = {};
mc.search.entry = "";
mc.search.box = [];
mc.search.box.y = 90;
mc.search.io = false;

mc.mainbg = {};
mc.mainbg.scene = "";
mc.mainbg.camera = "";
mc.mainbg.sphere = "";
mc.mainbg.mouse = {};
mc.mainbg.mouse.x = 10;
mc.mainbg.mouse.y = 10;
mc.mainbg.mouse.lastx = 1;
mc.mainbg.mouse.lasty = 1;

mc.viewingContent = false;

mc.d2d = false;
mc.d3d = false;

mc.urlChangeTimer = null;
mc.hashLoadingTimer = null;
mc.hashLoading = false;

mc.actions = {};
mc.visual = {};
mc.isAppleTouchDevice = (navigator.userAgent.match(/ipad|iphone/i));



// Scramble Writer modified from http://onehackoranother.com/projects/jquery/jquery-grab-bag/text-effects.html
mc.scrambledWriter.randomAlphaNum = function() {
        var rnd = Math.floor(Math.random() * 62);
        if (rnd >= 52) return String.fromCharCode(rnd - 4);
        else if (rnd >= 26) return String.fromCharCode(rnd + 71);
        else return String.fromCharCode(rnd + 65);
}
// if first character of regex expression match is tag opening, return value passed, otherwise return random alphanum
mc.scrambledWriter.tagCheck = function($0) {
	return $0[0] == '<' ? $0 : mc.scrambledWriter.randomAlphaNum();
}

$.fn.scrambledWriter = function() {
	// stop previous scrambler
	clearInterval(mc.scrambledWriterTimer);
	this.each(function() {
		// bug fix: when html scrambler contains divs, click now contains pass-through for quick word scrambler stop
		$(this).find('div').css('pointer-events', 'none');
		$(this).find('a').css('pointer-events', 'auto');
		
		var $ele = $(this),
			str = $ele.html(), 
			replace = /<[^>]+>|[^\s]/g,
			tag = mc.scrambledWriter.tagCheck,
			inc = 8;
		mc.scramble.progress = 0;
		$ele.html('');
		mc.scrambledWriterTimer = setInterval(function() {
			if (mc.scramble.progress <= -1) {
				// stop scrambler if progress = -1
				$ele.html(str).highlightSearchTerms(); // scrambled string is back to original with highlighted search terms;
				clearInterval(mc.scrambledWriterTimer);
			} else {
				if (str.substring(mc.scramble.progress, mc.scramble.progress + inc).indexOf('<') != -1) {
					// ensure none of the characters between progress and increment are tags, if there is one, move progress to end of tag
					var endTag = str.indexOf('>', mc.scramble.progress);
					if (endTag > -1) {
						mc.scramble.progress = endTag + 1;
					}
				}
				// Replace html element with first part of original string and remainder scrambled characters
				$ele.html(str.substring(0, mc.scramble.progress) + str.substring(mc.scramble.progress, str.length).replace(replace, tag));
				// increase progress counter
				mc.scramble.progress += inc;
				// when progress is greater than original string length, stop scrambler
				if (mc.scramble.progress >= str.length + inc) mc.scramble.progress = -1;
			}
		}, 30);
	});
	return this;
};

// new line to <br>
mc.scrambledWriter.nl2br = function (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

// if first character of regex expression match is tag opening, return value passed, otherwise return highlighted search term
mc.highlightSearchTerms_tagCheck = function($0) {
	return $0[0] == '<' ? $0 : '<span class="highlighted">' + $0 + '</span>';
}

// highlight search terms of jQuery element's html
$.fn.highlightSearchTerms = function() {
	var searchTerms = $.trim($("#e").val());
	if (searchTerms == '') return;
	searchTerms = searchTerms.split(' ');
	this.each(function() {
	var $element = $(this);
		$.each(searchTerms, function( index, value ) {
			$element.html(
				$element.html().replace(
					new RegExp('<[^>]+>|('+value+')', 'ig'),
					mc.highlightSearchTerms_tagCheck
				)
			);
		});
	});
}

mc.actions.enterMatrix = function() {
	$("#x").show().animate({
		opacity: 1
	}, 500);
	$(".search-hide").fadeOut(500);
	$("html, body").animate({ scrollTop: 0 }, 0);
	mc.matrix.io = true;
}
mc.actions.leaveMatrix = function() {
	$("#x").animate({
		opacity: 0
	}, 500, function() {
		$(this).hide();
	});
	$(".search-hide").fadeIn(500);
	mc.matrix.io = false;
}

// Document Ready
mc.init.elementTriggers = function() {
	
	if (!mc.firstPageLoad) return;
	mc.firstPageLoad = false;
	mc.actions.enterMatrix();
	
}

	$(document).on('focus', "#e", function() {
		if ($(".image-popup").length > 0 && $(".image-popup").is(':visible')) return;
		// Enter Matrix
		
		$("#e").animate({
			opacity: 0.75,
			width: Math.floor(0.5 * mc.x.area.width),
			left: Math.floor(mc.x.area.width / 2 -  Math.floor(0.5 * mc.x.area.width) / 2),
			top: Math.floor(mc.x.area.height / 2 - $(this).height() / 2)
		}, 500);
		mc.actions.enterMatrix();
	});
	
	/*
	$(document).on('focusout', "#e", function() {
		// Leave Matrix
		if (!mc.search.io) {
			$("#e").animate({
				opacity: 0.5,
				width: Math.floor(0.25 * mc.x.area.width),
				left: Math.floor(mc.x.area.width / 2 -  Math.floor(0.25 * mc.x.area.width) / 2),
				top: mc.search.box.y
			}, 500);
			mc.actions.leaveMatrix();
		}
	});
	*/
	
	$(document).on('keyup', "#e", function(event) {
		if (!mc.search.io) {
			mc.search.entry = $("#e").val();
			if (event.keyCode == 27) {
				$("#e").blur();
			} else if (event.keyCode == 13) {
				//var searchTerms = $(this).val().replace(/%20/g, '+').replace(/ /g, '+');
				window.location="https://google.com/search?q="+$(this).val();
			} else {
				var counter = 0;
				for (var i=0;i<mc.matrix.active.length;++i) {
					var searchedWord = ''+mc.matrix.active[i].word;
					if (searchedWord.indexOf(mc.search.entry) != -1) counter++;
					if (counter >= 5) return;
				}
				var counter = 0;
				for (var i=0;i<mc.matrix.wordset.length;++i) {
					var searchedWord = ''+mc.matrix.wordset[i];
					if (searchedWord.indexOf(mc.search.entry) != -1) {
						var activeWord = [];
						activeWord.ref = i;
						activeWord.word = mc.matrix.wordset[activeWord.ref];
						activeWord.x = Math.floor(Math.random() * 5 / mc.matrix.grid.x);
						activeWord.len = activeWord.word.length;
						activeWord.y = Math.floor(Math.random() * mc.x.area.height / mc.matrix.grid.y);
						activeWord.speed = Math.floor(Math.random() * 2 + 1);
						activeWord.xdiff = 0;
						activeWord.speed += Math.floor(Math.random() * 10 + 1);
						mc.matrix.active.push(activeWord);
						counter++;
						if (counter >= 5) return;
					}
				}
			
			}
		}
	});

mc.init.matrix = function() {
	var no2d = false;
	mc.x.canvas = $('#x').first()[0];
	try {
		x = mc.x.canvas.getContext('2d');
	} catch (e) {
		no2d = true;
	}

	mc.actions.resize();
	$("#e").fadeIn();

	$.ajax({
		url: "/files/words.en.txt",
		dataType: 'text',
		context: document.body,
		success: function(data, textStatus, jqXHR) {
			data = data.split("\n");
			for(var i = 0;i < data.length;++i){
				if ($.trim(data[i]) != '')
					mc.matrix.wordset.push($.trim(data[i]));
			}
			mc.matrix.fps = setTimeout(function() {mc.matrix.redraw();}, (1));
		}
	});
}

// Window Resize
$(window).resize(function() {
	mc.actions.resize();
});

mc.actions.resize = function() {

	mc.x.area.width = $(window).width();
	mc.x.area.height = $(window).height();
	$("#x").attr("width", mc.x.area.width);
	$("#x").attr("height", mc.x.area.height);
	mc.matrix.max = mc.x.area.width / 20;

	if (mc.matrix.io) {
		$("#e").css('top', mc.x.area.height / 2 - $("#e").height() / 2);
		$("#e").css('left', mc.x.area.width / 2 - $("#e").width() / 2);
		$("#e").css('width', Math.floor(0.5 * mc.x.area.width));
	} else {
		$("#e").css('width', Math.floor(0.25 * mc.x.area.width));
		$("#e").css('left', mc.x.area.width / 2 - $("#e").width() / 2);
		$("#e").css('top', '90px');
	}
	
	
	// must replace with css media query
	if (mc.x.area.width < 630) {
		$("#logo-text").hide();
	} else {
		$("#logo-text").show();
	}

	$("#content").css("width", $("#wrapper").innerWidth());

}

mc.matrix.redraw = function() {
	var timeStart = new Date();

	x.clearRect(0, 0, mc.x.area.width, mc.x.area.height);
	if (mc.matrix.io) {
		if (mc.matrix.active.length < mc.matrix.max) {
			while (mc.matrix.active.length < mc.matrix.max) {
				var activeWord = [];
				activeWord.ref = Math.floor(Math.random() * mc.matrix.wordset.length);
				activeWord.word = mc.matrix.wordset[activeWord.ref];
				if (mc.matrix.firstDraw) {
					activeWord.x = Math.floor(Math.random() * mc.x.area.width / mc.matrix.grid.x);
					activeWord.len = Math.floor(Math.random() * activeWord.word.length);
				} else {
					activeWord.x = Math.floor(Math.random() * 5 / mc.matrix.grid.x);
					activeWord.len = 0;
				}
				activeWord.y = Math.floor(Math.random() * mc.x.area.height / mc.matrix.grid.y);
				activeWord.speed = Math.floor(Math.random() * 2 + 1);
				activeWord.xdiff = 0;
				activeWord.speed += Math.floor(Math.random() * 10 + 1);
				mc.matrix.active.push(activeWord);
			}
		}
		
		mc.matrix.firstDraw = false;
		
		for (key in mc.matrix.active) {
			var activeWord = mc.matrix.active[key];
			activeWord.xdiff += activeWord.speed;
			if (Math.floor(activeWord.x * mc.matrix.grid.x + activeWord.xdiff) > mc.x.area.width) {
				mc.matrix.active.splice(key, 1);
			}
			if (activeWord.len < activeWord.word.length) {
				if (Math.floor(Math.random() * 100 + 1) > 80) {
					mc.matrix.active[key].len++;
				}
			}
			if (typeof(mc.matrix.active[key]) != 'undefined' && mc.matrix.active[key].len > 0) {
				for (var char_index = 0; char_index < activeWord.len; ++char_index) {
					var char_display = '';
					var color_dim;
					var current_char = activeWord.word.substring(char_index, char_index + 1);
					var search_match_index = activeWord.word.toLowerCase().indexOf(mc.search.entry.toLowerCase());

					x.font = mc.matrix.font;
					
					if (mc.search.entry != "" && search_match_index != -1 && char_index >= search_match_index && char_index < search_match_index + mc.search.entry.length) {
						color_dim = '#0000'+Math.floor((Math.random() * 155 + 100)).toString(16)+'';
					} else if (mc.search.entry != "" && search_match_index != -1) {
						var yellodim = Math.floor((Math.random() * 155 + 100)).toString(16);
						color_dim = '#'+yellodim+yellodim+'ff';
						x.font = mc.matrix.font2;
					} else {
						var yellodim = Math.floor((Math.random() * 155 + 50)).toString(16);
						color_dim = '#00'+Math.floor((Math.random() * 155 +50 )).toString(16)+'ff';
						x.font = mc.matrix.font2;
					}
					x.fillStyle = color_dim;
					if (Math.floor(Math.random() * 100 + 1) < 3) {
						x.font = mc.matrix.font;
						char_display = Math.floor(Math.random() * 2);
					} else {						
						char_display = current_char;
					}
					x.fillText(char_display,
						Math.floor(activeWord.x * mc.matrix.grid.x + char_index * 20 + activeWord.xdiff),
						Math.floor(activeWord.y * mc.matrix.grid.y));
				}
			}
		}	
	}
	setTimeout(function() {mc.matrix.redraw()}, (1000/20) - (new Date() - timeStart));
}

mc.preloadImage = function(image_filenames) {
	images = [];
	for (i = 0; i < image_filenames.length; i++) {
		images[i] = new Image();
		images[i].src = image_filenames[i];
	}
}

mc.encodeUrlName = function(url) {
	url = url.replace(/ /g, '+').replace(/%20/g, '+');
	url = url.replace(/\W+/g, "-");
	while (url.indexOf("--") > -1) {
		url = url.replace("--", "-");
	}
	url = url.toLowerCase();
	return url;
}

mc.actions.changeLocation = function(url) {
	url = url.replace(/ /g, '+').replace(/%20/g, '+');
	try {
		window.clearTimeout(mc.urlChangeTimer);
		window.clearTimeout(mc.hashLoadingTimer);
	} catch (e) {}
	mc.loadingNewUrl = true;
	window.location = url;
	ga('send','pageview', "/" + url);
	mc.urlChangeTimer = window.setTimeout(function() {mc.loadingNewUrl = false;}, 1000);
}

$(function() {
	mc.init.matrix(); // words matrix
	$('#e').focus();
});