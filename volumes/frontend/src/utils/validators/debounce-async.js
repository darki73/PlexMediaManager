export default function debounceAsync(validator, delay) {
    let currentTimer = null;
    let currentPromiseReject = null;

    function debounce() {
        return new Promise((resolve, reject) => {
            currentTimer = setTimeout(() => {
                currentTimer = null;
                currentPromiseReject = null;
                resolve();
            }, delay);
            currentPromiseReject = reject;
        });
    }

    return function (value) {
        if (currentTimer) {
            currentPromiseReject(new Error('replaced'));
            clearTimeout(currentTimer);
            currentTimer = null;
        }
        return validator.call(this, value, debounce);
    };
}
