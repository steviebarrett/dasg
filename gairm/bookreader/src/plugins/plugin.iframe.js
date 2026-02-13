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

  window.addEventListener('message', function (event) {

    // explicit origin allowlist
    if (
        event.origin !== 'https://dasg.ac.uk' &&
        event.origin !== 'https://dev.dasg.ac.uk' &&
        event.origin !== 'http://dasg.localhost'
    ) {
      return;
    }

    // Optional: accept only messages from the same window (no iframe here)
    if (event.source !== window) {
      return;
    }

    // Not a recognized message type, abort
    if (!event.data || event.data.type !== MESSAGE_TYPE_FRAGMENT_CHANGE) {
      return;
    }

    // Ensure fragment is a string before using
    if (typeof event.data.fragment !== 'string') {
      return;
    }

    br.updateFromParams(br.paramsFromFragment(event.data.fragment));
  });
}
