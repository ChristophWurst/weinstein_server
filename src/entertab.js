import $ from 'jquery';

// Map [Enter] key to work like the [Tab] key
// Daniel P. Clark 2014

// Catch the keydown for the entire document
$(document).keydown(e => {

    // Set self as the current item in focus
    let self = $(':focus');
    // Set the form by the current item in focus
    const form = self.parents('form:eq(0)');
    let focusable;

    // Array of Indexable/Tab-able items
    focusable = form.find('input,a,select,button').filter(':visible');

    if (e.which === 13) { // [Enter] key
        if (self.is('input:submit')) {
            form.submit();
        }
        if (self.is('button') || self.is('a.btn')) {
            return true;
        }

        // If not a regular hyperlink/button/textarea
        if ($.inArray(self, focusable) && (!self.is('a,button'))) {
            // Then prevent the default [Enter] key behaviour from submitting the form
            e.preventDefault();
        } // Otherwise follow the link/button as by design, or put new line in textarea

        // Focus on the next item (either previous or next depending on shift)
        var idx = focusable.index(self);
        if (idx === -1) {
            // Element not found, which means it's not an input
            // -> probably s2 element, so let's find the actual select element
            self = self.closest('div').find('select');
            var idx = focusable.index(self);
        }
        var next = focusable.eq(idx + (e.shiftKey ? -1 : 1));
        if (next.hasClass("select2-hidden-accessible")) {
            next.select2('open');
            next.focus();
        } else {
            next.focus();
        }

        return false;
    }
});
