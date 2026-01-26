define([
	'jquery',
	'slick'
	], function ($) {
		'use strict';

		$.widget('magepow.suggest', {
			options: {
				itemsNumber: 0
			},

			_create: function () {
				var options = this.options,
					items0 = (1 > options.itemsNumber) ? options.itemsNumber : 1,
					items600 = (2 > options.itemsNumber) ? options.itemsNumber : 2,
					items1000 = (3 > options.itemsNumber) ? options.itemsNumber : 3;
				var $this  = $(this),
					slider = $('.ajax-cart-owl-carousel');
				slider.on('init', function(event, slick){
				    $this._hideLoading();
				});
				slider.slick({
					'arrows'          : true,
					'dots'            : false,
					'infinite'        : true,
					'slidesToShow'    : items1000,
					'responsive'      : [{breakpoint: 999, settings: {slidesToShow: items600}}, {breakpoint: 599, settings: {slidesToShow: items0}}]
				});

			},

			_hideLoading: function () {
				$('.ajax-owl-loading').hide();
			}
		});

		return $.magepow.suggest;
	});