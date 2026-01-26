define([
    'jquery',
    'infinitescroll',
    'catalogAddToCart',
    'jquery/ui',
    'domReady!'
    ], function ($, infinitescroll, catalogAddToCart) {
    'use strict';
    $.widget('magepow.infinitescroll', {
        options: {
            container 	: ".main",
            item      	: ".product-item",
            pagination  : ".pages-items",
            next   		: ".next",
            delay  		: "600",
            src 		: "",
            htmlLoading : "<div class=\"iass-spinner\"><img src=\"{src}\"/><span><em>Loading - please wait...</em></span></div>",
            htmlLoadMore: "<div class=\"ias-trigger ias-trigger-next\"><button class=\"load-more\">Load more items</button></div>",
            htmlLoadEnd : "<div class=\"ias-noneleft\">{text}</div>",
            textLoadEnd: "<em>You've reached the end of the item.</em>",
            textLoadMore: "Load more items",
            textPrev 	: "Load more items",
            htmlPrev 	: "<div class=\"ias-trigger ias-trigger-prev\"><button class=\"load-more\">Load more items</button></div>",
            offset 		: 3
        },

        _create: function () {
        	var self = this;
            self._initScroll();
        },

        _initScroll: function () {
        	var options = this.options;
        	$(document).ready(function ($) {
	            $('body').addClass('infinitescroll-pro');
	            if(!jQuery().ias){
	            	console.warn('Plugin "jQuery.ias" does not exist!');
	            	return;
	            }
            	jQuery.ias('destroy');
	            window.ias = jQuery.ias({
	                container : options.container,
	                item 	  : options.item,
	                pagination: options.pagination,
		            pageHash  : true,
		            pageParam : "p",
	                next 	  : options.next,
	                delay     : options.delay
	            });
	            window.ias.extension(new IASSpinnerExtension({
	                src : options.src,
	                html: options.htmlLoading
	            }));
	            window.ias.extension(new IASNoneLeftExtension({
	                text: options.textLoadEnd,
	                html: options.htmlLoadEnd,
	            }));
	          	window.ias.extension(new IASTriggerExtension({
	                text    : options.textLoadMore,
	                html    : options.htmlLoadMore,
	                textPrev: options.textPrev,
	                htmlPrev: options.htmlPrev,
	                offset  : options.offset,
	            }));
	            window.ias.on('rendered', function(items){
	                $('body').trigger('contentUpdated');
	                $( "form[data-role='tocart-form']" ).catalogAddToCart();
	            });
            });
        }
    });

    return $.magepow.infinitescroll;
});
