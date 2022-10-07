const setCustomVocabUriLabel = function(select) {
  const value = select.closest(".value");
  const label = select.children(":selected").data("label");
  let labelInput = value.find(".custom-vocab-uri-label");
  if (!labelInput.length) {
    labelInput = $(
      `<input type="hidden" class="custom-vocab-uri-label" data-value-key="o:label">`
    );
    select.after(labelInput);
  }
  labelInput.attr("value", label ?? "");
};

$(document).on("o:prepare-value", function(e, type, value) {
  if (0 === type.indexOf("customvocab:")) {
    var thisValue = $(value);
    var selectTerms = thisValue.find("select.terms");
    selectTerms.chosen({
      width: "100%"
    });
    // Prepare URI types.
    const selects = thisValue.find("select.custom-vocab-uri");
    selects.each(function() {
      setCustomVocabUriLabel($(this));
    });
  }
});
$(document).on("o:prepare-value-annotation", function(e, type, value) {
  if (0 === type.indexOf("customvocab:")) {
    var thisValue = $(value);
    var selectTerms = thisValue.find("select.terms");
    selectTerms.chosen({
      width: "100%"
    });
    // Prepare URI types.
    const selects = thisValue.find("select.custom-vocab-uri");
    selects.each(function() {
      setCustomVocabUriLabel($(this));
    });
    // fix spacing
    thisValue.find(".chosen-container").css("height", "100%");
    thisValue.find(".chosen-search-input").css("min-height", "36px");
  }
});

$(document).on("change", "select.custom-vocab-uri", function(e) {
  setCustomVocabUriLabel($(this));
});
