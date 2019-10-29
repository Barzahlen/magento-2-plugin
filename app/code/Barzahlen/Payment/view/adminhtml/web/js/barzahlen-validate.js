require([
        'jquery',
        'mage/translate',
        'jquery/validate'],
    function($){
        $.validator.addMethod(
            'validate-max-value-decimal', function (value) {
                /*
                alert(parseFloat(value) + ' <= ' + parseFloat(999.99));
                alert(parseFloat(value) <= parseFloat(999.99));
                */
                return parseFloat(value) <= parseFloat(999.99);
            }, $.mage.__('Please enter only a maximum amount of 999.99 EUR'));
    }
);