if (!String.prototype.startsWith) {
    Object.defineProperty(String.prototype, 'startsWith', {
        enumerable: false,
        configurable: false,
        writable: false,
        value (searchString, position) {
            position = position || 0;
            return this.indexOf(searchString, position) === position;
        }
    });
}

/**
 * Trim specified string from the right side of the string
 * @param { string } string
 * @returns { string }
 */
if (!String.prototype.rtrim) {
    String.prototype.rtrim = function (string) {
        if (string === undefined) {
            string = '\\s';
        }
        return this.replace(new RegExp('[' + string + ']*$'), '');
    };
}

/**
 * Trim specified string from the left side of the string
 * @param { string } string
 * @returns { string }
 */
if (!String.prototype.ltrim) {
    String.prototype.ltrim = function (string) {
        if (string === undefined) {
            string = '\\s';
        }
        return this.replace(new RegExp('^[' + string + ']*'), '');
    };
}

/**
 * Replace all occurrences of character/string in string
 * @param { string } search
 * @param { string } replacement
 * @returns { string }
 */
if (!String.prototype.replaceAll) {
    String.prototype.replaceAll = function (search, replacement) {
        let target = this;
        return target.replace(new RegExp(search, 'g'), replacement);
    };
}

/**
 * Capitalize first character of the string
 * @returns { string }
 */
if (!String.prototype.ucfirst) {
    String.prototype.ucfirst = function() {
        let string = this;
        if (
            string === null
            || string === undefined
            || string.length === 0
        ) {
            return '';
        }
        return string.charAt(0).toUpperCase() + string.slice(1);
    };
}
