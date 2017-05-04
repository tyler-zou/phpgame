define(["moment"], function(moment) {

    /**
     * SzTime, declaration.
     */
    var SzTime = function() {};

    /**
     * Get the timestamp of now.
     *
     * @return {Number} timestamp
     */
    SzTime.prototype.getTime = function() {
        return moment().format('X');
    };

    /**
     * Get time string of 'YYYY-mm-dd HH:ii:ss' of now.
     *
     * @return {String} time
     */
    SzTime.prototype.setTime = function() {
        return moment().format('YYYY-MM-DD HH:mm:ss');
    };

    return new SzTime();

});