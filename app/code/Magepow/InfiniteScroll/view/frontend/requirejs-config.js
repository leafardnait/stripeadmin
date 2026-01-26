
var config = {
	map: {
        '*': {
            magepowInfinitescroll: 'Magepow_InfiniteScroll/js/infinitescroll',
            infinitescroll: 'Magepow_InfiniteScroll/js/plugin/infinitescroll',
        }
    },
	paths: {
		'magepow/infinitescroll': 'Magepow_InfiniteScroll/js/plugin/infinitescroll',
	},
	shim: {
		'magepowInfinitescroll': {
			deps: ['jquery', 'infinitescroll']
		},
		'magepow/infinitescroll': {
			deps: ['jquery']
		},
	}

};