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

  const ALLOWED_MESSAGE_ORIGINS = new Set([
    'https://dasg.ac.uk',
    'https://dev.dasg.ac.uk',
    'http://dasg.localhost',
  ]);

  const allowedSourceWindow = window;

  window.addEventListener('message', (event) => {
    if (!ALLOWED_MESSAGE_ORIGINS.has(event.origin)) return;
    if (event.source !== allowedSourceWindow) return;

    const data = event.data;
    if (!data || typeof data !== 'object') return;
    if (data.type !== MESSAGE_TYPE_FRAGMENT_CHANGE) return;
    if (typeof data.fragment !== 'string') return;

    br.updateFromParams(br.paramsFromFragment(data.fragment));
  });
}
