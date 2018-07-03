/*
Add it as collection pre-request script and then in each request below it add the following headers:

X-API-HASH : {{regiondoHmacAuthHeader}}
X-API-TIME : {{regiondoReqTime}}
X-API-ID : <yourPublicKey>

*/
function getQueryString(url) {
    var arrSplit = url.split('?');
    return arrSplit.length > 1 ? url.substring(url.indexOf('?')+1) : '';
}

function getAuthHeader(requestUrl) {
  var PUBLIC_KEY = '<yourPublicKey>';
  var SECRET_KEY = '<yourPrivateKey>';

    var queryString = getQueryString(requestUrl);

    var timestamp = Date.now();
    pm.environment.set("regiondoReqTime", timestamp);

    var requestData = timestamp+PUBLIC_KEY+queryString;
    console.log("Message 2 be hashed with private key: " + requestData);

    var hmacDigest = CryptoJS.enc.Hex.stringify(CryptoJS.HmacSHA256(requestData, SECRET_KEY));

    var authHeader = hmacDigest;
    return authHeader;
}

var hmac = getAuthHeader(request['url']);
console.log("HmacSHA256-Hash: " + hmac);

postman.setEnvironmentVariable('regiondoHmacAuthHeader', hmac);
