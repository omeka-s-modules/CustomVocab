$(document).on('o:prepare-value', function(e, type, value) {
    if (0 === type.indexOf('customvocab:')) {
        var thisValue = $(value);
        var selectTerms = thisValue.find('select.terms');
        selectTerms.chosen({
            width: "100%"
        });
    }
});
