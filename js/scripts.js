var shorturlfe = (function() {
    "use strict";
    // private functions

    return {
        // public functions
        
        copyToClipboard: function(){
            let self = shorturlfe;
            /* Get the text field */
            const copyText = document.querySelector('#short-url-fe input#short-url-fe-textbox');

            /* Select the text field */
            self.selectText(copyText);
            let clipboard = navigator.clipboard;
            /* Copy the text inside the text field */
            if(clipboard){
                navigator.clipboard.writeText(copyText.value);
            }else{  // fallback to a DEPRECATED feature
                console.log('WARNING! "navigation.clipboard" API is not available. This usually happens when you are NOT using a SSL domain. Copy text to clipboard is still possible, but using a deprecated function that may stop working at any time. Please, use a SSL domain');
                document.execCommand('copy');
            }
        },
        selectText: function(theInput){
            theInput.select();
            theInput.setSelectionRange(0, 99999); /* For mobile devices */
        }
    };
})();