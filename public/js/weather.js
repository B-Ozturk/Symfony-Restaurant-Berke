link = "https://api.openweathermap.org/data/2.5/weather?q=amsterdam&units=metric&apikey=dc989810ec5879216998f7685d8d2057";
let request = new XMLHttpRequest();
request.open('GET',link,true);
request.onload = function(){
    let obj = JSON.parse(this.response);
    if (request.status >= 200 && request.status < 400) {
        let temp = obj.main.temp;
        let temp2=Math.ceil(temp);
        let feel=obj.main.feels_like;
        document.querySelector("#feel").innerHTML="Voelt aan als:"+feel+"&#176;c";

        document.querySelector("#max").innerHTML="Max:"+obj.main.temp_max+"&#176;c";
        document.querySelector("#min").innerHTML="Min:"+obj.main.temp_min+"&#176;c";

        console.log(temp+" graden");
        console.log(feel);
        console.log(obj)
        let icon=obj.weather[0].icon;
        console.log(obj.weather[0].icon);
        console.log(obj.name);
        let name= document.querySelector("#tem").innerHTML=obj.name;
        let t=document.querySelector("#temp").innerHTML=temp2+"&#176;c";
        let img=document.querySelector("#img");
        img.setAttribute("src","http://openweathermap.org/img/wn/"+icon+"@2x.png")


    }
}
request.send();