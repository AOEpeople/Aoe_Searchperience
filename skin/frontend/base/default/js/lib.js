if (typeof String.prototype.trim !== 'function') {
	String.prototype.trim = function () {
		return this.replace(/^\s+|\s+$/g, '');
	}
}

if (typeof Array.prototype.indexOf !== 'function') {
	Array.prototype.indexOf = function (elt /*, from*/) {
		var len = this.length >>> 0;

		var from = Number(arguments[1]) || 0;
		from = (from < 0)
				? Math.ceil(from)
				: Math.floor(from);
		if (from < 0)
			from += len;

		for (; from < len; from++) {
			if (from in this &&
				this[from] === elt)
				return from;
		}
		return -1;
	};
}

if (typeof Array.prototype.in_array !== 'function') {
	Array.prototype.in_array = function (needle) {
		for (var i = 0; i < this.length; i++) if (this[ i] === needle) return true;
		return false;
	};
}

/**
 * This method can be used to restore an object recursivly from a json string
 */
RestoreableObject = function () {
	this.classname = 'RestoreableObject';

	this.restoreFromJson = function (data) {
		for (key in data) {
			if (this[key] !== 'undefined') {
				if (typeof this[key] == 'object') {
					var object = this[key];
					object.restoreFromJson = this.restoreFromJson;
					object.restoreFromJson(data[key]);
				} else if ((typeof (this[key])) == 'function') {
					//nothing todo
				} else {
					this[key] = data[key];
				}
			}
		}

	};
};
