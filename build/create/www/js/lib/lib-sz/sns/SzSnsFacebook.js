define(["SzSns"], function(SzSns) {

    /**
     * Facebook implementation of SzSns.
     */
    var SzSnsFacebook = function() {
        // Call the parent constructor
        SzSns.call(this);
    };
    // inherit SzSns
    SzSnsFacebook.prototype = new SzSns();
    // correct the constructor pointer because it points to SzSns
    SzSnsFacebook.prototype.constructor = SzSnsFacebook;

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* API Function Overwrites
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @see SzSns.prototype.getUserInfo
     */
    SzSnsFacebook.prototype.getUserInfo = function() {
        // TODO to be implemented!
    };

    //...

    return new SzSnsFacebook();

});