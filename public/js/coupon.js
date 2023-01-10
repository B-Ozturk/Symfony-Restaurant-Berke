var mysql = require('mysql');

var con = mysql.createConnection({
    host: "localhost",
    user: "berke",
    password: "berke"
});

con.query("SELECT * FROM coupon", function (err, result, fields) {
    if (err) throw err;
    console.warn(result);
});