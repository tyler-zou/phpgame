define(["SzTime"], function(SzTime) {

    /**
     * EfLogger, declaration.
     */
    var SzLogger = function() {};

    /**
     * Log message.
     *
     * @param {String|Object} msg
     * @return {void}
     */
    SzLogger.prototype.log = function(msg) {
        if (typeof console === 'object') {
            if (typeof msg == 'string') {
                console.log('[' + SzTime.setTime() + '] ' + msg);
            } else {
                console.log('[' + SzTime.setTime() + ']: ', msg);
            }
        }
    };

    return new SzLogger();

});