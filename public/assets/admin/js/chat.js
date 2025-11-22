
(function () {
    "use strict";

        var chatBodyType = document.getElementById("ChatBody");
        new SimpleBar(chatBodyType, { autoHide: true });
        
        var profileDetailsMain = document.getElementById("profile-details-main");
        new SimpleBar(profileDetailsMain, { autoHide: true });

        var profileDetailsMain = document.getElementById("main-chat-list");
        new SimpleBar(profileDetailsMain, { autoHide: true });

       
})();