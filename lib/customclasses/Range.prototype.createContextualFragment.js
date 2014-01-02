/**
 * ie9 no longer supports Range.prototype.createContextualFragment which extjs relies on.
 * The following code will provide this function if it doesn't exist.
 */
if (typeof Range.prototype.createContextualFragment == "undefined") {
    Range.prototype.createContextualFragment = function (html) {
        var doc = window.document;
        var container = doc.createElement("div");
        container.innerHTML = html;
        var frag = doc.createDocumentFragment(), n;
        while ((n = container.firstChild)) {
            frag.appendChild(n);
        }
        return frag;
    };
}