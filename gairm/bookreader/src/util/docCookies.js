/**
 * Cookie helper module: get, set, remove
 * - Adds SameSite=Lax by default
 * - Adds Secure automatically on HTTPS (or when bSecure=true)
 * - Fixes removeItem (no dependency on missing hasItem)
 */

export function getItem(sKey) {
  if (!sKey) return null;

  const key = encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, '\\$&');
  const re = new RegExp('(?:(?:^|.*;)\\s*' + key + '\\s*\\=\\s*([^;]*).*$)|^.*$');
  const val = document.cookie.replace(re, '$1');
  return val ? decodeURIComponent(val) : null;
}

export function setItem(sKey, sValue, vEnd, sPath, sDomain, bSecure) {
  if (!sKey) return false;

  // Defaults
  const path = sPath || '/';
  const sameSite = 'Lax';

  // Secure if explicitly requested OR if we are on HTTPS
  const secure = (bSecure === true) || (typeof location !== 'undefined' && location.protocol === 'https:');

  let cookie =
      encodeURIComponent(sKey) + '=' + encodeURIComponent(String(sValue)) +
      `; path=${path}` +
      `; SameSite=${sameSite}`;

  if (vEnd instanceof Date) {
    cookie += `; expires=${vEnd.toUTCString()}`;
  }

  if (sDomain) {
    cookie += `; domain=${sDomain}`;
  }

  if (secure) {
    cookie += '; Secure';
  }

  document.cookie = cookie;
  return true;
}

export function removeItem(sKey, sPath, sDomain, bSecure) {
  if (!sKey) return false;

  const path = sPath || '/';
  const sameSite = 'Lax';
  const secure = (bSecure === true) || (typeof location !== 'undefined' && location.protocol === 'https:');

  let cookie =
      encodeURIComponent(sKey) + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT' +
      `; path=${path}` +
      `; SameSite=${sameSite}`;

  if (sDomain) {
    cookie += `; domain=${sDomain}`;
  }

  if (secure) {
    cookie += '; Secure';
  }

  document.cookie = cookie;
  return true;
}