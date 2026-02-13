/* global BookReader */
/**
 * Plugin for two-way communication between a BookReader in an IFrame and the
 * parent web page
 */

const MESSAGE_TYPE_FRAGMENT_CHANGE = 'bookReaderFragmentChange';

BookReader.prototype.init = (function (super_) {
  return function () {
    super_.call(this);
    _attachEventListeners(this);
  };
})(BookReader.prototype.init);

/**
 * @private
 * Using window.postMessage() and event listeners, the plugin notifies the
 * parent window when pages change, and the parent window can also
 * explicitly request a page change by sending its own message.
 *
 * @param {BookReader} br
 * @param {Window?} [parent]
 */
export function _attachEventListeners(br, parent = window.parent) {
  // Not embedded, abort
  if (!parent) {
    return;
  }

  br.bind(BookReader.eventNames.fragmentChange, () => {
    const fragment = br.fragmentFromParams(br.paramsFromCurrent());

    parent.postMessage(
      { type: MESSAGE_TYPE_FRAGMENT_CHANGE, fragment },
      '*'
    );
  });

  window.addEventListener('message', event => {

    // 1️⃣ Validate origin
    if (event.origin !== window.location.origin) {
      return;
    }

    // 2️⃣ Validate structure
    if (
        !event.data ||
        typeof event.data !== 'object' ||
        event.data.type !== MESSAGE_TYPE_FRAGMENT_CHANGE ||
        typeof event.data.fragment !== 'string'
    ) {
      return;
    }

    br.updateFromParams(
        br.paramsFromFragment(event.data.fragment)
    );
  });
}
