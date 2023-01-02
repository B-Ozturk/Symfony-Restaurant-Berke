// console.warn("Javscript werkt op iedere pagina");

var now = new Date();
var delay = 60 * 60 * 1000; // 1 hour in msec
var start = delay - (now.getMinutes() * 60 + now.getSeconds()) * 1000 + now.getMilliseconds();

setTimeout(function doSomething() {
    console.warn("uurtje factuurtje")
    setTimeout(doSomething, delay);
}, start);