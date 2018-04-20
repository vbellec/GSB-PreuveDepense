/**
 * Promisify AJAX request
 * 
 * @see https://developer.mozilla.org/fr/docs/Web/API/XMLHttpRequest
 * @async
 * @function ajaxPromise
 * @param {!string} method HTTP verb of the request
 * @param {!string} url URL of the request
 * @param {FormData|undefined} paramsData Data to send
 * @returns {Promise} Result of the request
 */
const ajaxPromise = function (method, url, paramsData) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url);
    xhr.onload = () => {
      if (xhr.status >= 200 && xhr.status < 300) {
        resolve(xhr.response);
      } else {
        reject({
          status: xhr.status,
          statusText: xhr.statusText
        });
      }
    };
    xhr.onerror = () => {
      reject({
        status: xhr.status,
        statusText: xhr.statusText
      });
    };
    if (paramsData) {
      xhr.send(paramsData);
    } else {
      xhr.send();
    }
  });
};
