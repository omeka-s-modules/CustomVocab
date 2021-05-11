$(document).on('o:prepare-value', function(e, type, value) {
    if (0 === type.indexOf('customvocab:')) {
        var thisValue = $(value);
        var selectTerms = thisValue.find('select.terms');
        selectTerms.chosen({
            width: "100%"
        });
    }
});

$(document).on('change', 'select.custom-vocab-uri', function(e) {
    const thisSelect = $(this);
    const label = thisSelect.children(':selected').data('label');
    const labelInput = $(`<input type="hidden" data-value-key="o:label">`);
    labelInput.attr('value', label);
    thisSelect.after(labelInput);
});

