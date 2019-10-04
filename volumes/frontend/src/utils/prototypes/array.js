import some from 'lodash/some';

/**
 * Check if item exists in array
 * @param item
 * @returns {boolean}
 */
Array.prototype.inArray = function(item) {
    return some(this, item);
};

/**
 * Push item to the array only if it does not exists in it already
 * @param element
 */
Array.prototype.uniquePush = function(element) {
    if (!this.inArray(element)) {
        this.push(element);
    }
};
