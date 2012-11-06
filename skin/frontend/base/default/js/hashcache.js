function AoeSolrHashCache() {
	var self = this;

	this.init = function(size) {
		self.size = size;
		self.cache = new Array();
		self.keys = new Array();
	};

	this.has = function(hash) {
		if(hash=='') { hash == 'empty'; };
		hash = decodeURIComponent(hash);
		var result = false;

		if(self.keys.indexOf(hash) != -1) {
			result = true;
		}

		return result;
	};

	this.get = function(hash) {
		if(hash=='') { hash == 'empty'; };
		hash = decodeURIComponent(hash);
		var result = null;

		if(self.has(hash)) {
			var key = self.keys.indexOf(hash);
			result = self.cache[key];
		}

		return result;
	};

	this.set = function(hash,content) {
		if(hash=='') { hash == 'empty'; };
		hash = decodeURIComponent(hash);

		if(!self.has(hash)) {
			self.keys.push(hash);
		}

		var key = self.keys.indexOf(hash);
		self.cache[key] = content;

			//when the cache size reaches the capacity
			//we shift the key and the cache map
		if(self.cache.length > self.size ){
			self.keys.shift();
			self.cache.shift();
		}

	};
};