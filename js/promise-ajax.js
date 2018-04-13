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

const ajaxGetJSONPromise = function (url) {
  return new Promise((resolve, reject) => {
    ajaxGetPromise(url)
      .then(data => resolve(JSON.parse(data)))
      .catch(err => reject(err));
  });
};